<?php

namespace App\Domains\Listing\DTOs;

use DateTimeImmutable;

final readonly class UpdateListingRequestData
{
    public function __construct(
        public int $listingId,
        public int $farmerId,
        public ?string $cropType = null,
        public ?int $qty = null,
        public ?string $location = null,
        public ?DateTimeImmutable $harvestDate = null,
        public ?string $photoUrl = null,
        public ?float $pricePerUnit = null,
        public ?float $startingPrice = null,
        public ?DateTimeImmutable $closingTime = null,
    ) {}

    public static function fromArray(array $data, int $listingId, int $farmerId, ?string $photoUrl = null): self
    {
        return new self(
            listingId: $listingId,
            farmerId: $farmerId,
            cropType: $data['crop_type'] ?? null,
            qty: isset($data['qty']) ? (int) $data['qty'] : null,
            location: $data['location'] ?? null,
            harvestDate: isset($data['harvest_date']) ? new DateTimeImmutable($data['harvest_date']) : null,
            photoUrl: $photoUrl,
            pricePerUnit: isset($data['price_per_unit']) ? (float) $data['price_per_unit'] : null,
            startingPrice: isset($data['starting_price']) ? (float) $data['starting_price'] : null,
            closingTime: isset($data['closing_time']) ? new DateTimeImmutable($data['closing_time']) : null,
        );
    }
}