<?php

namespace App\Domains\Bidding\Services;

use App\Domains\Bidding\DTOs\BidDTO;
use App\Domains\Bidding\DTOs\BidFilterDTO;
use App\Domains\Bidding\DTOs\BidPageDTO;
use App\Domains\Bidding\DTOs\BidSummaryDTO;
use App\Domains\Bidding\DTOs\RejectBidDTO;
use App\Domains\Bidding\DTOs\SubmitBidDTO;
use App\Domains\Bidding\Events\BidAccepted;
use App\Domains\Bidding\Events\BidPlaced;
use App\Domains\Bidding\Events\BidRejected;
use App\Domains\Bidding\Models\Bid;
use App\Enums\BidStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

/**
 * Error contract (mapped to HTTP status by the controller via the exception code):
 *   InvalidArgumentException  code 400 = bad input / rule violation, 404 = not found
 *   RuntimeException          code 403 = not allowed / not yours,    409 = conflict / wrong state
 */
class BidService implements BidServiceInterface
{
    /** Values of the Listing domain's selling-method and status columns that Bidding relies on. */
    private const SELLING_METHOD_TIMED_BIDDING = 'bidding';
    private const LISTING_STATUS_ACTIVE = 'active';

    public function submitBid(SubmitBidDTO $dto): BidDTO
    {
        $bid = DB::transaction(function () use ($dto) {
            // Lock the listing row so two simultaneous bids cannot both pass the
            // "minimum next bid" check against the same highest bid.
            $listing = $this->findListing($dto->listingId, lock: true);

            if ($this->enumValue($listing->selling_method) !== self::SELLING_METHOD_TIMED_BIDDING) {
                throw new InvalidArgumentException('This listing is not open for bidding.', 400);
            }
            if ((int) $listing->farmer_id === $dto->buyerId) {
                throw new RuntimeException('You cannot bid on your own listing.', 403);
            }
            if ($this->enumValue($listing->status) !== self::LISTING_STATUS_ACTIVE || ! $this->isBiddingOpen($listing)) {
                throw new RuntimeException('Bidding on this listing has closed.', 409);
            }
            if ($this->cents($dto->quantity) > $this->cents($listing->qty)) {
                throw new InvalidArgumentException(
                    sprintf('Only %s kg is available on this listing.', number_format((float) $listing->qty, 2)),
                    400
                );
            }

            $minimum = $this->minimumNextBid($listing);
            if ($this->cents($dto->amount) < $this->cents($minimum)) {
                throw new InvalidArgumentException(
                    sprintf('Bid must be at least LKR %s per kg.', number_format($minimum, 2)),
                    400
                );
            }

            return Bid::query()->create([
                'listing_id' => $listing->getKey(),
                'buyer_id' => $dto->buyerId,
                'bid_amount' => $dto->amount,
                'bid_qty' => $dto->quantity,
                'bid_status' => BidStatus::Pending,
            ]);
        });

        BidPlaced::dispatch($bid);

        return BidDTO::fromModel($bid->load('buyer'));
    }

    public function getListingBidSummary(int $listingId): BidSummaryDTO
    {
        $listing = $this->findListing($listingId);

        $highest = $this->highestActiveBid($listing->getKey());
        $isOpen = $this->enumValue($listing->selling_method) === self::SELLING_METHOD_TIMED_BIDDING
            && $this->enumValue($listing->status) === self::LISTING_STATUS_ACTIVE
            && $this->isBiddingOpen($listing);

        return new BidSummaryDTO(
            listingId: (int) $listing->getKey(),
            highestBid: $highest,
            totalBids: Bid::query()->where('listing_id', $listing->getKey())->count(),
            minimumNextBid: $this->minimumNextBid($listing),
            biddingClosesAt: $listing->closing_time 
                ? Carbon::parse($listing->closing_time )->toIso8601String()
                : null,
            isOpen: $isOpen,
        );
    }

    public function getBidsForListing(int $listingId, int $farmerId, BidFilterDTO $filter): BidPageDTO
    {
        $listing = $this->findListing($listingId);
        $this->assertOwnedBy($listing, $farmerId);

        $paginator = Bid::query()
            ->with('buyer')
            ->where('listing_id', $listing->getKey())
            ->when($filter->status, fn ($q, $status) => $q->where('bid_status', $status->value))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($filter->perPage);

        return BidPageDTO::fromPaginator($paginator);
    }

    public function getBuyerBids(int $buyerId, BidFilterDTO $filter): BidPageDTO
    {
        $paginator = Bid::query()
            ->with('buyer')
            ->where('buyer_id', $buyerId)
            ->when($filter->status, fn ($q, $status) => $q->where('bid_status', $status->value))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($filter->perPage);

        return BidPageDTO::fromPaginator($paginator);
    }

    public function getBid(int $bidId, int $userId): BidDTO
    {
        $bid = Bid::query()->with('buyer')->find($bidId);
        if (! $bid) {
            throw new InvalidArgumentException('Bid not found.', 404);
        }

        $listing = $this->findListing((int) $bid->listing_id);
        if ((int) $bid->buyer_id !== $userId && (int) $listing->farmer_id !== $userId) {
            throw new RuntimeException('You do not have access to this bid.', 403);
        }

        return BidDTO::fromModel($bid);
    }

    public function acceptBid(int $bidId, int $farmerId): BidDTO
    {
        $bid = DB::transaction(function () use ($bidId, $farmerId) {
            $unlocked = Bid::query()->find($bidId);
            if (! $unlocked) {
                throw new InvalidArgumentException('Bid not found.', 404);
            }

            // Lock order is always listing -> bid (same as submitBid) to avoid deadlocks.
            $listing = $this->findListing((int) $unlocked->listing_id, lock: true);
            $this->assertOwnedBy($listing, $farmerId);

            $bid = Bid::query()->lockForUpdate()->findOrFail($bidId);

            if ($bid->bid_status !== BidStatus::Pending) {
                throw new RuntimeException("This bid has already been {$bid->bid_status->value}.", 409);
            }
            // A bid can only be accepted once bidding has closed (rejecting is the opposite: open period only).
            if ($this->isBiddingOpen($listing)) {
                throw new RuntimeException(
                    sprintf(
                        'Bids can only be accepted after bidding closes (%s).',
                        Carbon::parse($listing->closing_time)->format('d M Y, H:i')
                    ),
                    409
                );
            }
            if ($this->cents($bid->bid_qty) > $this->cents($listing->qty)) {
                throw new RuntimeException('Not enough stock is available to accept this bid.', 409);
            }

            $bid->update([
                'bid_status' => BidStatus::Accepted,
                'decided_at' => now(),
            ]);

            // Dispatched inside the transaction on purpose — see BidAccepted docblock.
            BidAccepted::dispatch($bid);

            return $bid;
        });

        return BidDTO::fromModel($bid->load('buyer'));
    }

    public function rejectBid(RejectBidDTO $dto): BidDTO
    {
        $bid = DB::transaction(function () use ($dto) {
            $unlocked = Bid::query()->find($dto->bidId);
            if (! $unlocked) {
                throw new InvalidArgumentException('Bid not found.', 404);
            }

            $listing = $this->findListing((int) $unlocked->listing_id, lock: true);
            $this->assertOwnedBy($listing, $dto->farmerId);

            $bid = Bid::query()->lockForUpdate()->findOrFail($dto->bidId);

            if ($bid->bid_status !== BidStatus::Pending) {
                throw new RuntimeException("This bid has already been {$bid->bid_status->value}.", 409);
            }
            if (! $this->isBiddingOpen($listing)) {
                throw new RuntimeException('The bidding period has ended; bids can no longer be rejected.', 409);
            }

            $bid->update([
                'bid_status' => BidStatus::Rejected,
                'rejection_reason' => $dto->reason,
                'decided_at' => now(),
            ]);

            return $bid;
        });

        BidRejected::dispatch($bid);

        return BidDTO::fromModel($bid->load('buyer'));
    }

    /** @return class-string<Model> */
    private function listingModel(): string
    {
        return config('bidding.listing_model');
    }

    private function findListing(int $listingId, bool $lock = false): Model
    {
        $query = $this->listingModel()::query();
        if ($lock) {
            $query->lockForUpdate();
        }

        $listing = $query->find($listingId);
        if (! $listing) {
            throw new InvalidArgumentException('Listing not found.', 404);
        }

        return $listing;
    }

    private function assertOwnedBy(Model $listing, int $farmerId): void
    {
        if ((int) $listing->farmer_id !== $farmerId) {
            throw new RuntimeException('You do not own this listing.', 403);
        }
    }

    private function isBiddingOpen(Model $listing): bool
    {
        if (! $listing->closing_time) {
            return false;
        }

        return now()->lt(Carbon::parse($listing->closing_time));
    }

    /** Highest bid among pending + accepted bids (rejected bids never count). */
    private function highestActiveBid(int|string $listingId): ?float
    {
        $max = Bid::query()
            ->where('listing_id', $listingId)
            ->where('bid_status', '!=', BidStatus::Rejected->value)
            ->max('bid_amount');

        return $max === null ? null : (float) $max;
    }

    /** First bid: the starting price. After that: highest bid + configured increment. */
    private function minimumNextBid(Model $listing): float
    {
        $highest = $this->highestActiveBid($listing->getKey());

        if ($highest === null) {
            return round((float) $listing->starting_price, 2);
        }

        return round($highest + (float) config('bidding.min_increment', 5.00), 2);
    }

    /** Compare money as integer cents to avoid float rounding surprises. */
    private function cents(float|int|string|null $value): int
    {
        return (int) round(((float) $value) * 100);
    }

    private function enumValue(mixed $value): mixed
    {
        return $value instanceof \BackedEnum ? $value->value : $value;
    }
}
