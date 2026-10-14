<?php

namespace App\Console\Commands;

use App\Domains\Order\Models\Order;
use App\Domains\PriceHistory\Services\PriceHistoryServiceInterface;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Illuminate\Console\Command;

class BackfillPriceHistory extends Command
{
    protected $signature = 'price-history:backfill';
    protected $description = 'Record prices for every paid or completed order that is not in the price history yet';

    public function handle(PriceHistoryServiceInterface $priceHistory): int
    {
        $orders = Order::query()
            ->with('items.listing')
            ->where(fn ($q) => $q->where('payment_status', PaymentStatus::Paid->value)
                ->orWhere('status', OrderStatus::Completed->value))
            ->get();

        $recorded = 0;

        foreach ($orders as $order) {
            $recorded += $priceHistory->recordForOrder($order, $order->updated_at);
        }

        $this->info("Checked {$orders->count()} order(s), recorded/updated {$recorded} price line(s).");

        return self::SUCCESS;
    }
}
