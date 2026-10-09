<?php

namespace App\Domains\Bidding\DTOs;

use App\Enums\BidStatus;

final readonly class BidFilterDTO
{
    public function __construct(
        public ?BidStatus $status,
        public int $perPage,
    ) {}

    public static function fromArray(array $data): self
    {
        $status = isset($data['status']) && $data['status'] !== ''
            ? BidStatus::from($data['status'])
            : null;

        $perPage = (int) ($data['per_page'] ?? config('bidding.default_per_page', 15));
        $perPage = max(1, min($perPage, (int) config('bidding.max_per_page', 100)));

        return new self(status: $status, perPage: $perPage);
    }
}
