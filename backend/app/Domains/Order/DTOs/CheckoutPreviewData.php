<?php

namespace App\Domains\Order\DTOs;

final readonly class CheckoutPreviewData
{
    public function __construct(
        public string $farmerName,
        /** @var CheckoutPreviewItemData[] */
        public array $items,
        public float $totalAmount,
        public string $currency,
    ) {}
}
