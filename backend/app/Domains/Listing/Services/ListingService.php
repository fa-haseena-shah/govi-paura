<?php

namespace App\Domains\Listing\Services;

use App\Domains\Listing\DTOs\CreateListingRequestData;
use App\Domains\Listing\DTOs\FilterListingData;
use App\Domains\Listing\DTOs\ListingResponseData;
use App\Domains\Listing\DTOs\UpdateListingRequestData;
use App\Domains\Listing\Models\Listing;
use App\Domains\Notification\Services\SmsAlertServiceInterface;
use App\Enums\ListingStatus;
use App\Enums\SellingMethod;
use InvalidArgumentException;
use RuntimeException;

class ListingService implements ListingServiceInterface
{
    public function __construct(
        private readonly SmsAlertServiceInterface $smsAlertService,
    ) {}

    public function create(CreateListingRequestData $data): ListingResponseData
    {
        $this->assertSellingMethodFieldsValid(
            $data->sellingMethod,
            $data->pricePerUnit,
            $data->startingPrice,
            $data->closingTime,
        );

        $listing = Listing::create([
            'farmer_id' => $data->farmerId,
            'crop_type' => $data->cropType,
            'qty' => $data->qty,
            'price_per_unit' => $data->pricePerUnit,
            'harvest_date' => $data->harvestDate,
            'location' => $data->location,
            'photo_url' => $data->photoUrl,
            'selling_method' => $data->sellingMethod,
            'starting_price' => $data->startingPrice,
            'closing_time' => $data->closingTime,
            'status' => ListingStatus::Active,
        ]);

        return ListingResponseData::fromModel($listing);
    }

    public function update(UpdateListingRequestData $data): ListingResponseData
    {
        $listing = Listing::find($data->listingId);

        if ($listing === null) {
            throw new InvalidArgumentException('Listing not found.');
        }

        if ($listing->farmer_id !== $data->farmerId) {
            throw new RuntimeException('You do not have permission to edit this listing.');
        }

        if (in_array($listing->status, [ListingStatus::Closed, ListingStatus::Sold], true)) {
            throw new RuntimeException('Closed or sold listings cannot be edited.');
        }

        $updates = array_filter([
            'crop_type' => $data->cropType,
            'qty' => $data->qty,
            'location' => $data->location,
            'harvest_date' => $data->harvestDate,
            'photo_url' => $data->photoUrl,
            'price_per_unit' => $data->pricePerUnit,
            'starting_price'=> $data->startingPrice,
            'closing_time' => $data->closingTime,
        ], fn ($value) => $value !== null);

        // revalidate fields specific to the selling-method using the post-update values,
        // in case price/closing-time fields were changed.
        $this->assertSellingMethodFieldsValid(
            $listing->selling_method,
            $updates['price_per_unit'] ?? $listing->price_per_unit,
            $updates['starting_price'] ?? $listing->starting_price,
            $updates['closing_time'] ?? $listing->closing_time,
        );

        if (isset($updates['qty']) && $updates['qty'] === 0) {
            $updates['status'] = ListingStatus::Unavailable;
        } elseif (isset($updates['qty']) && $updates['qty'] > 0 && $listing->status === ListingStatus::Unavailable) {
            // restocking an out-of-stock listing puts it back on sale
            $updates['status'] = ListingStatus::Active;
        }

        $listing->update($updates);

        return ListingResponseData::fromModel($listing->fresh());
    }

    public function close(int $listingId, int $farmerId): ListingResponseData
    {
        $listing = Listing::find($listingId);

        if ($listing === null) {
            throw new InvalidArgumentException('Listing not found.');
        }

        if ($listing->farmer_id !== $farmerId) {
            throw new RuntimeException('You do not have permission to close this listing.');
        }

        if ($listing->status === ListingStatus::Closed) {
            throw new RuntimeException('This listing is already closed.');
        }

        $listing->update(['status' => ListingStatus::Closed]);

        return ListingResponseData::fromModel($listing->fresh());
    }

    public function viewDetails(int $listingId): ListingResponseData
    {
        $listing = Listing::with('farmer')->find($listingId);

        if ($listing === null) {
            throw new InvalidArgumentException('Listing not found.');
        }

        return ListingResponseData::fromModel($listing);
    }

    public function browse(FilterListingData $filter): array
    {
        $query = Listing::query()->with('farmer')->where('status', ListingStatus::Active);

        $query->where(function ($q) {
            $q->where('selling_method', '!=', SellingMethod::Bidding->value)
            ->orWhere('closing_time', '>', now());
        });
        
        if ($filter->sellingMethod !== null) {
            $query->where('selling_method', $filter->sellingMethod);
        }

        if ($filter->cropType !== null) {
            $query->where('crop_type', $filter->cropType);
        }

        if ($filter->region !== null) {
            $query->where('location', 'like', '%' . $filter->region . '%');
        }

        if ($filter->minPrice !== null) {
            $query->where('price_per_unit', '>=', $filter->minPrice);
        }

        if ($filter->maxPrice !== null) {
            $query->where('price_per_unit', '<=', $filter->maxPrice);
        }

        if ($filter->availableOnly) {
            $query->where('qty', '>', 0);
        }

        if ($filter->search !== null) {
            $query->where(function ($q) use ($filter) {
                $q->where('crop_type', 'like', '%' . $filter->search . '%')
                  ->orWhere('location', 'like', '%' . $filter->search . '%');
            });
        }

        $total = $query->count();

        $listings = $query
            ->forPage($filter->page, $filter->perPage)
            ->orderByDesc('created_at')
            ->get();

        return [
            'data'    => $listings->map(fn (Listing $l) => ListingResponseData::fromModel($l))->all(),
            'total'   => $total,
            'page'    => $filter->page,
            'perPage' => $filter->perPage,
        ];
    }

    public function forFarmer(int $farmerId): array
    {
        return Listing::with('farmer')
            ->where('farmer_id', $farmerId)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Listing $l) => ListingResponseData::fromModel($l))
            ->all();
    }

    public function reduceStock(int $listingId, int $quantityPurchased): void
    {
        $listing = Listing::find($listingId);

        if ($listing === null) {
            throw new InvalidArgumentException('Listing not found.');
        }

        if ($quantityPurchased > $listing->qty) {
            throw new RuntimeException('Insufficient stock for this purchase.');
        }

        $qtyBefore = $listing->qty;
        $newQty = $qtyBefore - $quantityPurchased;
        $threshold = config('govipaura.low_stock_threshold');

        $listing->update([
            'qty'    => $newQty,
            'status' => $newQty === 0 ? ListingStatus::Sold : $listing->status,
        ]);

        if ($qtyBefore > $threshold && $newQty <= $threshold) {
            $this->smsAlertService->sendLowStockAlert($listing->farmer, $listing->fresh());
        }
    }

    private function assertSellingMethodFieldsValid(
        SellingMethod $sellingMethod,
        ?float $pricePerUnit,
        ?float $startingPrice,
        ?\DateTimeInterface $closingTime,
    ): void {
        match ($sellingMethod) {
            SellingMethod::FixedPrice => $pricePerUnit === null
                ? throw new InvalidArgumentException('price_per_unit is required for fixed-price listings.')
                : null,
            SellingMethod::Bidding => match (true) {
                $startingPrice === null || $closingTime === null
                    => throw new InvalidArgumentException('starting_price and closing_time are required for bidding listings.'),
                $closingTime <= now()
                    => throw new InvalidArgumentException('closing_time must be in the future.'),
                default => null,
            },
        };
    }
}