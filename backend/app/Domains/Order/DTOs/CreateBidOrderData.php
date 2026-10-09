<?php

namespace App\Domains\Order\DTOs;

final readonly class CreateBidOrderData
{
    public function __construct(
        public int $buyerId,
        public int $bidId,
        public int $listingId,
        public int $quantity,
        public float $unitPrice,   // bids.bid_amount (price per unit)
    ) {}
}
