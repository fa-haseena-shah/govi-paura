<?php

namespace App\Domains\Delivery\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

// when a delivery changes status, this is dispatched
// only after the related database transaction occurs so rolled-back status changes dont generate a notification

class DeliveryStatusChanged implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    // only ids carried 
    public function __construct(public readonly int $deliveryId, public readonly int $orderId, public readonly string $status) {}
}
