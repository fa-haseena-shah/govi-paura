<?php

namespace App\Domains\Bidding\DTOs;

final readonly class SubmitBidDTO
{
    public function __construct(
        public int $listingId,
        public int $buyerId,
        public float $amount,
        public float $quantity,
    ) {}

    public static function fromArray(array $data, int $buyerId): self
    {
        return new self(
            listingId: (int) $data['listing_id'],
            buyerId: $buyerId,
            amount: round((float) $data['amount'], 2),
            quantity: round((float) $data['quantity'], 2),
        );
    }
}
