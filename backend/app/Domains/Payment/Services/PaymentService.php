<?php

namespace App\Domains\Payment\Services;

use App\Domains\Order\Models\Order;
use App\Domains\Order\Services\OrderServiceInterface;
use App\Domains\Payment\DTOs\PayOrderData;
use App\Domains\Payment\DTOs\PaymentResponseData;
use App\Domains\Payment\Events\PaymentSucceeded;
use App\Domains\Payment\Models\Payment;
use App\Domains\Payment\Models\Receipt;
use App\Domains\Subscription\Events\SubscriptionPaid;
use App\Domains\Subscription\Models\Subscription;
use App\Domains\Subscription\Services\SubscriptionServiceInterface;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\PaymentTransactionStatus;
use App\Enums\SubscriptionStatus;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class PaymentService implements PaymentServiceInterface
{
    public function __construct(
        private readonly OrderServiceInterface $orderService,
        private readonly SubscriptionServiceInterface $subscriptionService,
    ) {}

    public function payForOrder(PayOrderData $data): PaymentResponseData
    {
        $order = Order::find($data->orderId);

        if ($order === null) {
            throw new InvalidArgumentException('Order not found.');
        }

        if ($order->buyer_id !== $data->buyerId) {
            throw new RuntimeException('You do not have permission to pay for this order.');
        }

        if ($order->payment_status === PaymentStatus::Paid) {
            throw new RuntimeException('This order has already been paid for.');
        }

        return match ($data->method) {
            PaymentMethod::Card => $this->payByCard($order, $data->method),
            PaymentMethod::CashOnDelivery => $this->payByCashOnDelivery($order, $data->method),
        };
    }

    public function confirmCashCollected(int $orderId, int $confirmedByUserId): PaymentResponseData
    {
        $order = Order::find($orderId);

        if ($order === null) {
            throw new InvalidArgumentException('Order not found.');
        }

        // the farmer, or the rider who is delivering this order, can confirm the cash was handed over
        $isDeliveryRider = DB::table('deliveries')
            ->where('order_id', $order->id)
            ->where('rider_id', $confirmedByUserId)
            ->exists();

        if ($order->farmer_id !== $confirmedByUserId && !$isDeliveryRider) {
            throw new RuntimeException('You do not have permission to confirm payment for this order.');
        }

        $payment = Payment::where('payable_type', 'order')
            ->where('payable_id', $order->id)
            ->where('method', PaymentMethod::CashOnDelivery)
            ->first();

        if ($payment === null) {
            throw new InvalidArgumentException('No cash-on-delivery payment found for this order.');
        }

        if ($payment->status === PaymentTransactionStatus::Success) {
            throw new RuntimeException('Cash has already been confirmed as collected for this order.');
        }

        return DB::transaction(function () use ($payment, $order) {
            $payment->update(['status' => PaymentTransactionStatus::Success]);

            $receipt = Receipt::create([
                'payment_id' => $payment->id,
                'receipt_number' => 'RCPT-' . now()->format('Ymd') . '-' . str_pad((string) $payment->id, 5, '0', STR_PAD_LEFT),
                'amount' => $payment->amount,
                'issued_at' => now(),
            ]);

            $order->update(['payment_status' => PaymentStatus::Paid]);
            PaymentSucceeded::dispatch($order, $payment);   // notifies the farmer and the buyer

            return PaymentResponseData::fromModels($payment, $receipt);
        });
    }

    public function payForSubscription(int $subscriptionId, int $riderId, string $method): PaymentResponseData
    {
        // subscriptions are card-only (there is no delivery to collect cash on)
        if ($method !== PaymentMethod::Card->value) {
            throw new InvalidArgumentException('Subscriptions can only be paid by card.');
        }

        $subscription = Subscription::find($subscriptionId);

        if ($subscription === null) {
            throw new InvalidArgumentException('Subscription not found.');
        }

        if ($subscription->rider_id !== $riderId) {
            throw new RuntimeException('You do not have permission to pay for this subscription.');
        }

        if ($subscription->status !== SubscriptionStatus::Pending || $subscription->payment_status === PaymentStatus::Paid) {
            throw new RuntimeException('This subscription is not awaiting payment.');
        }

        // payment, receipt and activation succeed/fail together
        [$payment, $receipt] = DB::transaction(function () use ($subscription) {
            $payment = Payment::create([
                'reference' => 'SUB-' . $subscription->id . '-' . uniqid(),
                'payable_id' => $subscription->id,
                'payable_type' => 'subscription',
                'amount' => $subscription->fee,
                'currency' => 'LKR',
                'status' => PaymentTransactionStatus::Success,
                'method' => PaymentMethod::Card,
            ]);

            $receipt = Receipt::create([
                'payment_id' => $payment->id,
                'receipt_number' => 'RCPT-' . now()->format('Ymd') . '-' . str_pad((string) $payment->id, 5, '0', STR_PAD_LEFT),
                'amount' => $payment->amount,
                'issued_at' => now(),
            ]);

            $this->subscriptionService->activatePaid($subscription->id);

            return [$payment, $receipt];
        });

        SubscriptionPaid::dispatch($subscription->fresh(), $payment);   // notifies the rider

        return PaymentResponseData::fromModels($payment, $receipt);
    }

    // new order becomes "confirmed" when it gets paid. if an order is past pending, it can still be paid
    private function confirmIfPending(Order $order): void
    {
        if ($order->status === OrderStatus::Pending) {
            $this->orderService->updateStatus($order->id, $order->farmer_id, OrderStatus::Confirmed);
        }
    }

    private function payByCard(Order $order, PaymentMethod $method): PaymentResponseData
    {
        return DB::transaction(function () use ($order, $method) {
            $payment = Payment::create([
                'reference' => 'ORDER-' . $order->id . '-' . uniqid(),
                'payable_id' => $order->id,
                'payable_type' => 'order',
                'amount' => $order->total_amount,
                'currency' => 'LKR',
                'status' => PaymentTransactionStatus::Success,
                'method' => $method,
            ]);

            $receipt = Receipt::create([
                'payment_id' => $payment->id,
                'receipt_number' => 'RCPT-' . now()->format('Ymd') . '-' . str_pad((string) $payment->id, 5, '0', STR_PAD_LEFT),
                'amount' => $payment->amount,
                'issued_at' => now(),
            ]);

            $order->update(['payment_status' => PaymentStatus::Paid]);
            PaymentSucceeded::dispatch($order, $payment);   // notifies farmer and buyer
            $this->confirmIfPending($order);

            return PaymentResponseData::fromModels($payment, $receipt);
        });
    }

    private function payByCashOnDelivery(Order $order, PaymentMethod $method): PaymentResponseData
    {
        return DB::transaction(function () use ($order, $method) {
            $payment = Payment::create([
                'reference' => 'ORDER-' . $order->id . '-' . uniqid(),
                'payable_id' => $order->id,
                'payable_type' => 'order',
                'amount' => $order->total_amount,
                'currency' => 'LKR',
                'status' => PaymentTransactionStatus::Pending,
                'method' => $method,
            ]);

            // payment_status deliberately stays Unpaid — only confirmCashCollected() changes that
            $this->confirmIfPending($order);

            return PaymentResponseData::fromModels($payment); // no receipt for COD
        });
    }
}