<?php

namespace App\Domains\Delivery\Listeners;

use App\Domains\Delivery\Services\DeliveryServiceInterface;
use App\Domains\Order\Events\OrderCreated;
use Throwable;

// every new order gets a delivery straight away, so the 12-hour clock starts when the order is placed
class CreateDeliveryForOrder
{
    public function __construct(private readonly DeliveryServiceInterface $deliveries) {}

    public function handle(OrderCreated $event): void
    {
        try {
            $this->deliveries->ensureForOrder($event->order);
        } catch (Throwable $e) {
            // never break order placement; deliveries:enforce-sla creates any that are missing
            report($e);
        }
    }
}
