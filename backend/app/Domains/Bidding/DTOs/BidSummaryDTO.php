<?php

namespace App\Domains\Bidding\DTOs;

final readonly class BidSummaryDTO
{
    public function __construct(
        public int $listingId,
        public ?float $highestBid,
        public int $totalBids,
        public float $minimumNextBid,
        public ?string $biddingClosesAt,
        public bool $isOpen,
    ) {}

    public function toArray(): array
    {
        return [
            'listing_id' => $this->listingId,
            'highest_bid' => $this->highestBid,
            'total_bids' => $this->totalBids,
            'minimum_next_bid' => $this->minimumNextBid,
            'bidding_closes_at' => $this->biddingClosesAt,
            'is_open' => $this->isOpen,
        ];
    }
}
