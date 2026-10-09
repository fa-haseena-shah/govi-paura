<?php

namespace App\Domains\Listing\DTOs;

use App\Domains\Listing\Models\Listing;
use App\Enums\ListingStatus;
use App\Enums\SellingMethod;

final readonly class ListingResponseData
{
    public function __construct(
        public int $id,
        public int $farmerId,
        public string $cropType,
        public int $qty,
        public ?float $pricePerUnit,
        public string $harvestDate,
        public string $location,
        public ?string $photoUrl,
        public SellingMethod $sellingMethod,
        public ?float $startingPrice,
        public ?string $closingTime,
        public ListingStatus $status,
        public ?string $farmerName = null,
    ) {}

    public static function fromModel(Listing $listing): self
    {
        return new self(
            id: $listing->id,
            farmerId: $listing->farmer_id,
            cropType: $listing->crop_type,
            qty: $listing->qty,
            pricePerUnit: $listing->price_per_unit !== null ? (float) $listing->price_per_unit : null,
            harvestDate: $listing->harvest_date->format('Y-m-d'),
            location: $listing->location,
            photoUrl: $listing->photo_url,
            sellingMethod: $listing->selling_method,
            startingPrice: $listing->starting_price !== null ? (float) $listing->starting_price : null,
            closingTime: $listing->closing_time?->toIso8601String(),
            status: $listing->status,
            farmerName: $listing->farmer?->full_name,
        );
    }
}