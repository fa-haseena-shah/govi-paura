<?php

namespace App\Domains\Payment\Services;

use App\Domains\Order\Models\Order;
use App\Domains\Order\Services\OrderServiceInterface;
use App\Domains\Payment\DTOs\PayOrderData;
use App\Domains\Payment\DTOs\PaymentResponseData;
use App\Domains\Payment\Models\Payment;
use App\Domains\Payment\Models\Receipt;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\PaymentTransactionStatus;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class PaymentService implements PaymentServiceInterface
{
    public function __construct(
        private readonly OrderServiceInterface $orderService,
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

        if ($order->farmer_id !== $confirmedByUserId) {
            throw new RuntimeException('You do not have permission to confirm payment for this order.');
            // this should move to whichever rider is assigned to the delivery, not the farmer.
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
                'payment_id'     => $payment->id,
                'receipt_number' => 'RCPT-' . now()->format('Ymd') . '-' . str_pad((string) $payment->id, 5, '0', STR_PAD_LEFT),
                'amount'         => $payment->amount,
                'issued_at'      => now(),
            ]);

            $order->update(['payment_status' => PaymentStatus::Paid]);

            return PaymentResponseData::fromModels($payment, $receipt);
        });
    }

    public function payForSubscription(int $subscriptionId, int $riderId, string $method): PaymentResponseData
    {
        throw new RuntimeException('Subscription payments are not available yet.');
    }

    // A new order is moved to "confirmed" when it gets paid. An order that is already past
    // "pending" (e.g. the farmer confirmed it first) can still be paid and keeps its status.
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

            return PaymentResponseData::fromModels($payment); // no receipt yet for COD
        });
    }
}