<?php

namespace App\Domains\Rating\DTOs;

final readonly class RatingSummaryData
{
    public function __construct(
        public ?float $average,
        public int $count,
        /** @var array<int, int> reviews per star */
        public array $distribution,
    ) {}
}
