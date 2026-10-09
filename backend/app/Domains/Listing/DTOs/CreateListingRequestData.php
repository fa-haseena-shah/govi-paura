<?php

namespace App\Domains\Listing\DTOs;

use App\Enums\SellingMethod;
use DateTimeImmutable;

final readonly class CreateListingRequestData
{
    public function __construct(
        public int $farmerId,
        public string $cropType,
        public int $qty,
        public string $location,
        public DateTimeImmutable $harvestDate,
        public SellingMethod $sellingMethod,
        public ?string $photoUrl = null,
        public ?float $pricePerUnit = null,
        public ?float $startingPrice = null,
        public ?DateTimeImmutable $closingTime = null,
    ) {}

    public static function fromArray(array $data, int $farmerId, ?string $photoUrl = null): self
    {
        return new self(
            farmerId: $farmerId,
            cropType: $data['crop_type'],
            qty: (int) $data['qty'],
            location: $data['location'],
            harvestDate: new DateTimeImmutable($data['harvest_date']),
            sellingMethod: SellingMethod::from($data['selling_method']),
            photoUrl: $photoUrl,
            pricePerUnit: isset($data['price_per_unit']) ? (float) $data['price_per_unit'] : null,
            startingPrice: isset($data['starting_price']) ? (float) $data['starting_price'] : null,
            closingTime: isset($data['closing_time']) ? new DateTimeImmutable($data['closing_time']) : null,
        );
    }
}