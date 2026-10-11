<?php

namespace App\Domains\Order\Events;

use App\Domains\Order\Models\Order;
use App\Enums\OrderStatus;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * An order moved to a new status (confirmed, processing, delivered, completed).
 * Fires after the surrounding database transaction commits, so a rolled-back change never notifies anyone.
 */
class OrderStatusChanged implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly Order $order, public readonly OrderStatus $previous) {}
}
