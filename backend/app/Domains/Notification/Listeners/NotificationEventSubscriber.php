<?php

namespace App\Domains\Notification\Listeners;

use App\Domains\Bidding\Events\BidPlaced;
use App\Domains\Bidding\Events\BidRejected;
use App\Domains\Delivery\Events\DeliveryStatusChanged;
use App\Domains\Listing\Events\BiddingClosed;
use App\Domains\Listing\Events\StockLow;
use App\Domains\Notification\Services\NotificationServiceInterface;
use App\Domains\Order\Events\OrderCreated;
use App\Domains\Order\Events\OrderStatusChanged;
use App\Domains\Order\Models\Order;
use App\Domains\Payment\Events\PaymentSucceeded;
use App\Domains\Payment\Models\Receipt;
use App\Enums\BidStatus;
use App\Enums\NotificationType;
use App\Enums\OrderSource;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Models\User;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\DB;
use Throwable;

// turns events fired by each domain into in-app notifications

class NotificationEventSubscriber
{
    public function __construct(private readonly NotificationServiceInterface $notifications) {}

    public function subscribe(Dispatcher $events): array
    {
        return [
            BidPlaced::class => 'onBidPlaced',
            BidRejected::class => 'onBidRejected',
            BiddingClosed::class => 'onBiddingClosed',
            OrderCreated::class => 'onOrderCreated',
            OrderStatusChanged::class => 'onOrderStatusChanged',
            PaymentSucceeded::class => 'onPaymentSucceeded',
            StockLow::class => 'onStockLow',
            DeliveryStatusChanged::class => 'onDeliveryStatusChanged',
        ];
    }

    // farmer - buyer bid on their listing
    public function onBidPlaced(BidPlaced $event): void
    {
        $this->safely(function () use ($event) {
            $bid = $event->bid->loadMissing('listing', 'buyer.buyerProfile');
            $listing = $bid->listing;

            if ($listing === null || $bid->buyer === null) {
                return;
            }

            $this->notifications->notify(
                $listing->farmer_id,
                NotificationType::BidPlaced,
                "New bid on {$listing->crop_type}",
                sprintf('%s bid LKR %s/kg for %s kg on your %s listing.',
                    $this->buyerName($bid->buyer), $this->number($bid->bid_amount), $this->number($bid->bid_qty), $listing->crop_type),
                ['bid_id' => $bid->id, 'listing_id' => $listing->id],
            );
        });
    }

    // buyer - bid was rejected
    public function onBidRejected(BidRejected $event): void
    {
        $this->safely(function () use ($event) {
            $bid = $event->bid->loadMissing('listing');

            $reason = $bid->rejection_reason ? " Reason: {$bid->rejection_reason}" : '';

            $this->notifications->notify(
                $bid->buyer_id,
                NotificationType::BidRejected,
                'Your bid was rejected',
                sprintf('Your bid of LKR %s/kg on %s was rejected.%s', $this->number($bid->bid_amount), $bid->listing?->crop_type ?? 'the listing', $reason),
                ['bid_id' => $bid->id, 'listing_id' => $bid->listing_id],
            );
        });
    }

    // farmer - bidding on listing has ended
    public function onBiddingClosed(BiddingClosed $event): void
    {
        $this->safely(function () use ($event) {
            $listing = $event->listing;

            $waiting = DB::table('bids')
                ->where('listing_id', $listing->id)
                ->where('bid_status', BidStatus::Pending->value)
                ->count();

            $this->notifications->notify(
                $listing->farmer_id,
                NotificationType::BiddingClosed,
                "Bidding closed: {$listing->crop_type}",
                $waiting === 0
                    ? "Bidding on your {$listing->crop_type} listing has closed. No bids are waiting for your decision."
                    : "Bidding on your {$listing->crop_type} listing has closed. You have {$waiting} bid(s) to review.",
                ['listing_id' => $listing->id],
            );
        });
    }


    // farmer and buyer - new order
    public function onOrderCreated(OrderCreated $event): void
    {
        $this->safely(function () use ($event) {
            $order = $event->order->loadMissing('items.listing', 'buyer.buyerProfile', 'farmer');
            $summary = $this->itemsSummary($order);
            $total = $this->number($order->total_amount);
            $data = array_filter(['order_id' => $order->id, 'bid_id' => $order->bid_id]);

            if ($order->source === OrderSource::AcceptedBid) {
                // the farmer created this order by accepting the bid, so only the buyer is notified
                $this->notifications->notify(
                    $order->buyer_id,
                    NotificationType::OrderCreated,
                    'Your bid was accepted',
                    "{$order->farmer->full_name} accepted your bid. Order #{$order->id} ({$summary}, LKR {$total}) has been created.",
                    $data,
                );

                return;
            }

            $this->notifications->notify(
                $order->buyer_id,
                NotificationType::OrderCreated,
                "Order #{$order->id} placed",
                "Your order with {$order->farmer->full_name} ({$summary}) for LKR {$total} was placed.",
                $data,
            );

            $this->notifications->notify(
                $order->farmer_id,
                NotificationType::OrderCreated,
                "New order #{$order->id}",
                "{$this->buyerName($order->buyer)} ordered {$summary} for LKR {$total}.",
                $data,
            );
        });
    }

    // buyer - order status changed i.e. processing, delivered, etc
    public function onOrderStatusChanged(OrderStatusChanged $event): void
    {
        $this->safely(function () use ($event) {
            $order = $event->order;

            $text = match ($order->status) {
                OrderStatus::Confirmed => 'has been confirmed.',
                OrderStatus::Processing => 'is being prepared.',
                OrderStatus::Delivered => 'has been delivered.',
                OrderStatus::Completed => 'is complete. Thank you for buying on Govi Paura!',
                default => "is now {$order->status->value}.",
            };

            $this->notifications->notify(
                $order->buyer_id,
                NotificationType::OrderStatusUpdated,
                "Order #{$order->id} " . $order->status->value,
                "Your order #{$order->id} {$text}",
                ['order_id' => $order->id, 'status' => $order->status->value],
            );
        });
    }

    // farmer and buyer - order payment succeeded
    public function onPaymentSucceeded(PaymentSucceeded $event): void
    {
        $this->safely(function () use ($event) {
            $order = $event->order->loadMissing('buyer.buyerProfile');
            $payment = $event->payment;

            $amount = $this->number($payment->amount);
            $method = $payment->method === PaymentMethod::CashOnDelivery ? 'cash on delivery' : 'card';
            $receipt = Receipt::where('payment_id', $payment->id)->value('receipt_number');
            $data = ['order_id' => $order->id, 'payment_id' => $payment->id];

            $this->notifications->notify(
                $order->buyer_id,
                NotificationType::PaymentCompleted,
                'Payment confirmed',
                "Your {$method} payment of LKR {$amount} for order #{$order->id} was received." . ($receipt ? " Receipt {$receipt}." : ''),
                $data,
            );

            $this->notifications->notify(
                $order->farmer_id,
                NotificationType::PaymentCompleted,
                'Payment received',
                "{$this->buyerName($order->buyer)} paid LKR {$amount} ({$method}) for order #{$order->id}.",
                $data,
            );
        });
    }

    // farmer - low stock / listing sold out
    public function onStockLow(StockLow $event): void
    {
        $this->safely(function () use ($event) {
            $listing = $event->listing;
            $left = (int) $listing->qty;

            $this->notifications->notify(
                $listing->farmer_id,
                NotificationType::LowStock,
                $left === 0 ? "Sold out: {$listing->crop_type}" : "Low stock: {$listing->crop_type}",
                $left === 0
                    ? "Your {$listing->crop_type} listing is sold out."
                    : "Only {$left} kg of {$listing->crop_type} left on your listing.",
                ['listing_id' => $listing->id, 'qty' => $left],
            );
        });
    }

    // buyer and farmer - delivery of their order changed status
    public function onDeliveryStatusChanged(DeliveryStatusChanged $event): void
    {
        $this->safely(function () use ($event) {
            $order = Order::find($event->orderId);

            if ($order === null) {
                return;
            }

            $label = str_replace('_', ' ', $event->status);
            $data = ['order_id' => $order->id, 'delivery_id' => $event->deliveryId, 'status' => $event->status];

            foreach ([$order->buyer_id, $order->farmer_id] as $userId) {
                $this->notifications->notify(
                    $userId,
                    NotificationType::DeliveryStatusUpdated,
                    "Delivery update: order #{$order->id}",
                    "Delivery for order #{$order->id} is now {$label}.",
                    $data,
                );
            }
        });
    }

    // private hlper functions
    private function safely(callable $work): void
    {
        try {
            $work();
        } catch (Throwable $e) {
            report($e);
        }
    }

    private function buyerName(User $buyer): string
    {
        $buyer->loadMissing('buyerProfile');

        return $buyer->buyerProfile?->business_name ?? $buyer->full_name;
    }

    private function itemsSummary(Order $order): string
    {
        return $order->items->map(fn ($item) => "{$item->listing->crop_type} x{$item->qty}")->implode(', ');
    }

    private function number(float|int|string $value): string
    {
        return rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.');
    }
}
