<?php

namespace App\Domains\Subscription\Events;

use App\Domains\Subscription\Models\Subscription;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

// subscriptions last day passed and its status became expired
class SubscriptionExpired implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly Subscription $subscription) {}
}
