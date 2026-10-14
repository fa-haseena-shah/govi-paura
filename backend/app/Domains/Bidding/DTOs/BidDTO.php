<?php

namespace App\Domains\Bidding\DTOs;

use App\Domains\Bidding\Models\Bid;
use App\Domains\Rating\DTOs\RatingBadgeData;

final readonly class BidDTO
{
    public function __construct(
        public int $id,
        public int $listingId,
        public int $buyerId,
        public ?string $buyerName,
        public RatingBadgeData $buyerRating,
        public float $amount,
        public float $quantity,
        public float $totalValue,
        public string $status,
        public ?string $rejectionReason,
        public ?string $decidedAt,
        public string $createdAt,
    ) {}

    public static function fromModel(Bid $bid): self
    {
        $buyer = $bid->buyer;
        $amount = (float) $bid->bid_amount;
        $quantity = (float) $bid->bid_qty;

        return new self(
            id: (int) $bid->id,
            listingId: (int) $bid->listing_id,
            buyerId: (int) $bid->buyer_id,
            buyerName: $buyer?->name,
            buyerRating: RatingBadgeData::fromUser($buyer),
            amount: $amount,
            quantity: $quantity,
            totalValue: round($amount * $quantity, 2),
            status: $bid->bid_status->value,
            rejectionReason: $bid->rejection_reason,
            decidedAt: $bid->decided_at?->toIso8601String(),
            createdAt: $bid->created_at->toIso8601String(),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'listing_id' => $this->listingId,
            'buyer_id' => $this->buyerId,
            'buyer_name' => $this->buyerName,
            'buyer_rating' => [
                'average' => $this->buyerRating->average,
                'count' => $this->buyerRating->count,
            ],
            'amount' => $this->amount,
            'quantity' => $this->quantity,
            'total_value' => $this->totalValue,
            'status' => $this->status,
            'rejection_reason' => $this->rejectionReason,
            'decided_at' => $this->decidedAt,
            'created_at' => $this->createdAt,
        ];
    }
}
