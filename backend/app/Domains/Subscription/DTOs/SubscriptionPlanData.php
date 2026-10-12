<?php

namespace App\Domains\Subscription\DTOs;

final readonly class SubscriptionPlanData
{
    public function __construct(
        public string $key,
        public string $name,
        public int $months,
        public float $fee,
        public string $currency,
        public string $description,
    ) {}
}
