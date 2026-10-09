<?php

namespace App\Domains\Order\DTOs;

use App\Domains\Order\Models\Order;
use App\Enums\OrderSource;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;

final readonly class OrderResponseData
{
    public function __construct(
        public int $id,
        public int $buyerId,
        public int $farmerId,
        public ?int $bidId,
        public float $totalAmount,
        public OrderStatus $status,
        public PaymentStatus $paymentStatus,
        public OrderSource $source,
        public ?string $buyerName,
        public ?string $buyerBusinessName,
        public ?string $buyerPhone,
        public ?string $farmerName,
        public ?string $paymentMethod,
        public ?string $transactionStatus,
        /** @var OrderItemData[] */
        public array $items,
        public string $createdAt,
    ) {}

    public static function fromModel(Order $order): self
    {
        $order->loadMissing(['items.listing', 'buyer.buyerProfile', 'farmer', 'payments']);

        // the most recent payment attempt decides what the UI shows (card / cash on delivery, pending / success)
        $payment = $order->payments->sortByDesc('id')->first();

        return new self(
            id: $order->id,
            buyerId: $order->buyer_id,
            farmerId: $order->farmer_id,
            bidId: $order->bid_id,
            totalAmount: (float) $order->total_amount,
            status: $order->status,
            paymentStatus: $order->payment_status,
            source: $order->source,
            buyerName: $order->buyer?->full_name,
            buyerBusinessName: $order->buyer?->buyerProfile?->business_name,
            buyerPhone: $order->buyer?->phone,
            farmerName: $order->farmer?->full_name,
            paymentMethod: $payment?->method->value,
            transactionStatus: $payment?->status->value,
            items: $order->items->map(fn ($item) => OrderItemData::fromModel($item))->all(),
            createdAt: $order->created_at->toIso8601String(),
        );
    }
}
