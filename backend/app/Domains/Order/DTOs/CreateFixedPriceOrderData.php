<?php

namespace App\Domains\Order\DTOs;

final readonly class CreateFixedPriceOrderData
{
    public function __construct(
        public int $buyerId,
        /** @var CartItemData[] */
        public array $items,
    ) {}

    public static function fromArray(array $data, int $buyerId): self
    {
        return new self(
            buyerId: $buyerId,
            items: CartItemData::manyFromArray($data['items']),
        );
    }
}
