<?php

namespace App\Domains\Payment\Events;

use App\Domains\Order\Models\Order;
use App\Domains\Payment\Models\Payment;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

// when payment for an order succeeded (card, or cash on delivery confirmed by the farmer), payment domain fires this after the related database transaction commits, so unnecessary changes dont notify
class PaymentSucceeded implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly Order $order, public readonly Payment $payment) {}
}
