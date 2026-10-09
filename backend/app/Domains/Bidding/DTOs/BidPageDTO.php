<?php

namespace App\Domains\Bidding\DTOs;

use Illuminate\Pagination\LengthAwarePaginator;

final readonly class BidPageDTO
{
    /** @param list<BidDTO> $bids */
    public function __construct(
        public array $bids,
        public int $currentPage,
        public int $lastPage,
        public int $perPage,
        public int $total,
    ) {}

    public static function fromPaginator(LengthAwarePaginator $paginator): self
    {
        return new self(
            bids: collect($paginator->items())
                ->map(fn ($bid) => BidDTO::fromModel($bid))
                ->values()
                ->all(),
            currentPage: $paginator->currentPage(),
            lastPage: $paginator->lastPage(),
            perPage: $paginator->perPage(),
            total: $paginator->total(),
        );
    }

    public function toArray(): array
    {
        return [
            'data' => array_map(fn (BidDTO $bid) => $bid->toArray(), $this->bids),
            'meta' => [
                'current_page' => $this->currentPage,
                'last_page' => $this->lastPage,
                'per_page' => $this->perPage,
                'total' => $this->total,
            ],
        ];
    }
}
