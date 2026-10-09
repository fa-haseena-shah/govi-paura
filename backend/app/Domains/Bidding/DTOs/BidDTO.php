<?php

namespace App\Domains\Bidding\DTOs;

use App\Domains\Bidding\Models\Bid;

final readonly class BidDTO
{
    public function __construct(
        public int $id,
        public int $listingId,
        public int $buyerId,
        public ?string $buyerName,
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
        $amount = (float) $bid->bid_amount;
        $quantity = (float) $bid->bid_qty;

        return new self(
            id: (int) $bid->id,
            listingId: (int) $bid->listing_id,
            buyerId: (int) $bid->buyer_id,
            buyerName: $bid->relationLoaded('buyer') ? $bid->buyer?->name : null,
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
