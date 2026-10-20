<?php

namespace App\Domains\PriceHistory\Listeners;

use App\Domains\Order\Events\OrderStatusChanged;
use App\Domains\Payment\Events\PaymentSucceeded;
use App\Domains\PriceHistory\Services\PriceHistoryServiceInterface;
use App\Enums\OrderStatus;
use App\Domains\Order\Models\Order;
use Illuminate\Support\Facades\Log;
use Throwable;

// saves the crop prices of an order into the price history when the transaction is complete:
//  - the order is paid (card, or cash on delivery confirmed), or
//  - the order reaches "completed"
class RecordTransactionPrice
{
    public function __construct(private readonly PriceHistoryServiceInterface $priceHistory) {}

    public function handle(PaymentSucceeded $event): void
    {
        $this->record($event->order);
    }

    public function handleStatusChange(OrderStatusChanged $event): void
    {
        if ($event->order->status === OrderStatus::Completed) {
            $this->record($event->order);
        }
    }

    private function record(Order $order): void
    {
        try {
            $this->priceHistory->recordForOrder($order->fresh());
        } catch (Throwable $e) {
            // the payment / status change already happened; a price-history problem must never break it
            Log::error('Could not record transaction prices for order ' . $order->id . ': ' . $e->getMessage());
        }
    }
}
