<?php

namespace App\Domains\Order\DTOs;

use App\Domains\Order\Models\OrderItem;

final readonly class OrderItemData
{
    public function __construct(
        public int $id,
        public int $listingId,
        public string $cropType,
        public int $qty,
        public float $unitPrice,
        public float $subTotal,
    ) {}

    public static function fromModel(OrderItem $item): self
    {
        return new self(
            id: $item->id,
            listingId: $item->listing_id,
            cropType: $item->listing->crop_type,
            qty: $item->qty,
            unitPrice: (float) $item->unit_price,
            subTotal: (float) $item->sub_total,
        );
    }
}
