<?php

namespace App\Domains\Subscription\Events;

use App\Domains\Payment\Models\Payment;
use App\Domains\Subscription\Models\Subscription;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

// riders subscription payment succeeded and the period was activated or scheduled
class SubscriptionPaid implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly Subscription $subscription, public readonly Payment $payment) {}
}
