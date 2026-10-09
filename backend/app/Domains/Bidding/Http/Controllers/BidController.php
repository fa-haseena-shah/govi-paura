<?php

namespace App\Domains\Bidding\Http\Controllers;

use App\Domains\Bidding\DTOs\BidFilterDTO;
use App\Domains\Bidding\DTOs\RejectBidDTO;
use App\Domains\Bidding\DTOs\SubmitBidDTO;
use App\Domains\Bidding\Http\Requests\AcceptBidRequest;
use App\Domains\Bidding\Http\Requests\ListListingBidsRequest;
use App\Domains\Bidding\Http\Requests\ListMyBidsRequest;
use App\Domains\Bidding\Http\Requests\RejectBidRequest;
use App\Domains\Bidding\Http\Requests\SubmitBidRequest;
use App\Domains\Bidding\Http\Requests\ViewBidRequest;
use App\Domains\Bidding\Services\BidServiceInterface;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

class BidController
{
    public function __construct(private readonly BidServiceInterface $bids) {}

    /** POST /api/bids — buyer submits a bid. */
    public function store(SubmitBidRequest $request): JsonResponse
    {
        try {
            $dto = SubmitBidDTO::fromArray($request->validated(), (int) $request->user()->id);
            $bid = $this->bids->submitBid($dto);

            return response()->json(['message' => 'Bid placed successfully.', 'data' => $bid->toArray()], 201);
        } catch (InvalidArgumentException|RuntimeException $e) {
            return $this->fail($e);
        }
    }

    /** GET /api/bids/my — buyer's own bids (history + status tracking). */
    public function myBids(ListMyBidsRequest $request): JsonResponse
    {
        $filter = BidFilterDTO::fromArray($request->validated());
        $page = $this->bids->getBuyerBids((int) $request->user()->id, $filter);

        return response()->json($page->toArray());
    }

    /** GET /api/listings/{listing}/bids — farmer's received bids / bid history for a listing. */
    public function listingBids(ListListingBidsRequest $request, int $listing): JsonResponse
    {
        try {
            $filter = BidFilterDTO::fromArray($request->validated());
            $page = $this->bids->getBidsForListing($listing, (int) $request->user()->id, $filter);

            return response()->json($page->toArray());
        } catch (InvalidArgumentException|RuntimeException $e) {
            return $this->fail($e);
        }
    }

    /** GET /api/listings/{listing}/bid-summary — highest bid, bid count, minimum next bid. */
    public function summary(ViewBidRequest $request, int $listing): JsonResponse
    {
        try {
            return response()->json(['data' => $this->bids->getListingBidSummary($listing)->toArray()]);
        } catch (InvalidArgumentException|RuntimeException $e) {
            return $this->fail($e);
        }
    }

    /** GET /api/bids/{bid} — one bid (bidder or the listing's farmer only). */
    public function show(ViewBidRequest $request, int $bid): JsonResponse
    {
        try {
            return response()->json(['data' => $this->bids->getBid($bid, (int) $request->user()->id)->toArray()]);
        } catch (InvalidArgumentException|RuntimeException $e) {
            return $this->fail($e);
        }
    }

    /** POST /api/bids/{bid}/accept — farmer accepts a bid. */
    public function accept(AcceptBidRequest $request, int $bid): JsonResponse
    {
        try {
            $result = $this->bids->acceptBid($bid, (int) $request->user()->id);

            return response()->json(['message' => 'Bid accepted.', 'data' => $result->toArray()]);
        } catch (InvalidArgumentException|RuntimeException $e) {
            return $this->fail($e);
        }
    }

    /** POST /api/bids/{bid}/reject — farmer rejects a bid. */
    public function reject(RejectBidRequest $request, int $bid): JsonResponse
    {
        try {
            $dto = RejectBidDTO::fromArray($request->validated(), $bid, (int) $request->user()->id);
            $result = $this->bids->rejectBid($dto);

            return response()->json(['message' => 'Bid rejected.', 'data' => $result->toArray()]);
        } catch (InvalidArgumentException|RuntimeException $e) {
            return $this->fail($e);
        }
    }

    /**
     * Map the service's exceptions onto the project's HTTP convention:
     *   InvalidArgumentException -> 400 (bad input) or 404 (not found)
     *   RuntimeException         -> 403 (not allowed) or 409 (conflict)
     * The exact status is carried in the exception code; anything else falls back to 400 / 409.
     */
    private function fail(Throwable $e): JsonResponse
    {
        $isInvalidArgument = $e instanceof InvalidArgumentException;
        $allowed = $isInvalidArgument ? [400, 404] : [403, 409];
        $status = in_array($e->getCode(), $allowed, true) ? $e->getCode() : ($isInvalidArgument ? 400 : 409);

        return response()->json(['message' => $e->getMessage()], $status);
    }
}
