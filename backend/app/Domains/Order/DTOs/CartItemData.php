<?php

namespace App\Domains\Order\DTOs;

final readonly class CartItemData
{
    public function __construct(
        public int $listingId,
        public int $quantity,
    ) {}

    /**
     * @param array<int, array{listing_id: int|string, quantity: int|string}> $items
     * @return self[]
     */
    public static function manyFromArray(array $items): array
    {
        return array_map(
            fn (array $item) => new self((int) $item['listing_id'], (int) $item['quantity']),
            array_values($items),
        );
    }
}
