<?php

namespace App\Domains\Subscription\DTOs;

/** 14.2: where a rider stands right now. */
final readonly class SubscriptionStatusData
{
    public function __construct(
        public string $state,
        public ?SubscriptionData $current,
        public ?SubscriptionData $upcoming,
        public ?SubscriptionData $pending,
        public ?string $coveredUntil,
        public ?string $expiredOn,
        public ?int $daysRemaining,
        public bool $canSubscribe,
        public bool $canRenew,
    ) {}
}
