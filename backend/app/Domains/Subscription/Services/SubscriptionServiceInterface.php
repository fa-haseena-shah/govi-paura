<?php

namespace App\Domains\Subscription\Services;

use App\Domains\Subscription\DTOs\SubscriptionData;
use App\Domains\Subscription\DTOs\SubscriptionPlanData;
use App\Domains\Subscription\DTOs\SubscriptionStatusData;
use App\Models\User;

interface SubscriptionServiceInterface
{
    /** @return SubscriptionPlanData[] */
    public function plans(): array;

    public function subscribe(User $rider, string $plan): SubscriptionData;

    public function renew(User $rider, ?string $plan): SubscriptionData;

    public function status(User $rider): SubscriptionStatusData;

    /** @return SubscriptionData[] */
    public function history(User $rider): array;

    public function cancel(User $rider, int $subscriptionId): SubscriptionData;

    public function activatePaid(int $subscriptionId): SubscriptionData;

    public function hasActiveSubscription(int $riderId): bool;

    /** @return array{activated: int, expired: int} */
    public function syncStatuses(?int $riderId = null): array;

    public function sendExpiryReminders(int $days): int;
}
