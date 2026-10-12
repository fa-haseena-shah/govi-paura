<?php

namespace App\Console\Commands;

use App\Domains\Subscription\Services\SubscriptionServiceInterface;
use Illuminate\Console\Command;

class UpdateSubscriptionStatuses extends Command
{
    protected $signature = 'subscriptions:update-statuses';
    protected $description = 'Start scheduled renewals, expire finished subscriptions and remind riders whose subscription ends soon';

    public function handle(SubscriptionServiceInterface $subscriptions): int
    {
        $result = $subscriptions->syncStatuses();
        $reminded = $subscriptions->sendExpiryReminders((int) config('subscriptions.reminder_days', 3));

        $this->info("Activated {$result['activated']}, expired {$result['expired']}, reminded {$reminded}.");

        return self::SUCCESS;
    }
}
