<?php

namespace App\Domains\Listing\DTOs;

use App\Enums\SellingMethod;

final readonly class FilterListingData
{
    public function __construct(
        public ?string $cropType = null,
        public ?string $region = null,
        public ?float $minPrice = null,
        public ?float $maxPrice = null,
        public bool $availableOnly = false,
        public ?string $search = null,
        public ?SellingMethod $sellingMethod = null,
        public int $page = 1,
        public int $perPage = 20,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            cropType: $data['crop_type'] ?? null,
            region: $data['region'] ?? null,
            minPrice: isset($data['min_price']) ? (float) $data['min_price'] : null,
            maxPrice: isset($data['max_price']) ? (float) $data['max_price'] : null,
            availableOnly: filter_var($data['available_only'] ?? false, FILTER_VALIDATE_BOOLEAN),
            search: $data['search'] ?? null,
            sellingMethod: SellingMethod::tryFrom((string) ($data['selling_method'] ?? '')),
            page: isset($data['page']) ? max(1, (int) $data['page']) : 1,
            perPage: isset($data['per_page']) ? min(100, max(1, (int) $data['per_page'])) : 20,
        );
    }
}