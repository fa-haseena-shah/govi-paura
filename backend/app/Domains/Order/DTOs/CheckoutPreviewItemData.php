<?php

namespace App\Domains\Order\DTOs;

final readonly class CheckoutPreviewItemData
{
    public function __construct(
        public int $listingId,
        public string $cropType,
        public int $quantity,
        public float $unitPrice,
        public float $subTotal,
        public int $availableStock,
    ) {}
}
