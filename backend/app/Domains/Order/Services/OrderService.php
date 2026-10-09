<?php

namespace App\Domains\Order\Services;

use App\Domains\Listing\Models\Listing;
use App\Domains\Listing\Services\ListingServiceInterface;
use App\Domains\Notification\Services\SmsAlertServiceInterface;
use App\Domains\Order\DTOs\CheckoutPreviewData;
use App\Domains\Order\DTOs\CheckoutPreviewItemData;
use App\Domains\Order\DTOs\CreateBidOrderData;
use App\Domains\Order\DTOs\CreateFixedPriceOrderData;
use App\Domains\Order\DTOs\OrderResponseData;
use App\Domains\Order\Models\Order;
use App\Enums\ListingStatus;
use App\Enums\OrderSource;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\SellingMethod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class OrderService implements OrderServiceInterface
{
    private const ALLOWED_NEXT = [
        'pending' => ['confirmed'],
        'confirmed' => ['processing'],
        'processing' => ['delivered'],
        'delivered' => ['completed'],
        'completed' => [],
    ];

    private const RELATIONS = ['items.listing', 'buyer.buyerProfile', 'farmer', 'payments'];

    public function __construct(
        private readonly ListingServiceInterface $listingService,
        private readonly SmsAlertServiceInterface $smsAlertService,
    ) {}

    public function createFromFixedPricePurchase(CreateFixedPriceOrderData $data): OrderResponseData
    {
        $order = DB::transaction(function () use ($data) {
            $quantities = $this->mergeQuantities($data->items);

            // lock the listing rows so two buyers cannot both take the last stock
            $listings = Listing::whereIn('id', array_keys($quantities))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            [$lines, $farmerId] = $this->resolveFixedPriceLines($quantities, $listings);

            $order = Order::create([
                'buyer_id' => $data->buyerId,
                'farmer_id' => $farmerId,
                'total_amount' => $this->sumLines($lines),
                'status' => OrderStatus::Pending,
                'payment_status' => PaymentStatus::Unpaid,
                'source' => OrderSource::FixedPricePurchase,
            ]);

            $order->items()->createMany($lines);

            // automatic stock reduction, one call per line
            foreach ($lines as $line) {
                $this->listingService->reduceStock($line['listing_id'], $line['qty']);
            }

            return $order;
        });

        return $this->finalizeNewOrder($order);
    }

    public function createFromAcceptedBid(CreateBidOrderData $data): OrderResponseData
    {
        $order = DB::transaction(function () use ($data) {
            $listing = Listing::whereKey($data->listingId)->lockForUpdate()->first();

            if ($listing === null) {
                throw new InvalidArgumentException('Listing not found.');
            }

            if ($data->quantity <= 0) {
                throw new InvalidArgumentException('Quantity must be positive.');
            }

            if ($data->quantity > $listing->qty) {
                throw new RuntimeException('Insufficient stock for this purchase.');
            }

            $subTotal = round($data->unitPrice * $data->quantity, 2);

            $order = Order::create([
                'buyer_id' => $data->buyerId,
                'farmer_id' => $listing->farmer_id,
                'bid_id' => $data->bidId,
                'total_amount' => $subTotal,
                'status' => OrderStatus::Pending,
                'payment_status' => PaymentStatus::Unpaid,
                'source' => OrderSource::AcceptedBid,
            ]);

            $order->items()->create([
                'listing_id' => $listing->id,
                'qty' => $data->quantity,
                'unit_price' => $data->unitPrice,
                'sub_total' => $subTotal,
            ]);

            $this->listingService->reduceStock($listing->id, $data->quantity);

            return $order;
        });

        // the farmer created this order themselves by accepting the bid, so no "new order" SMS
        return $this->finalizeNewOrder($order, notifyFarmer: false);
    }

    public function updateStatus(int $orderId, int $requesterId, OrderStatus $newStatus): OrderResponseData
    {
        $order = Order::find($orderId);

        if ($order === null) {
            throw new InvalidArgumentException('Order not found.');
        }

        if ($order->farmer_id !== $requesterId) {
            throw new RuntimeException('You do not have permission to update this order.');
        }

        $allowed = self::ALLOWED_NEXT[$order->status->value];

        if (!in_array($newStatus->value, $allowed, true)) {
            throw new InvalidArgumentException("Cannot transition from {$order->status->value} to {$newStatus->value}.");
        }

        $order->update(['status' => $newStatus]);

        return OrderResponseData::fromModel($order->fresh());
    }

    public function viewOrder(int $orderId, int $requesterId): OrderResponseData
    {
        $order = Order::with(self::RELATIONS)->find($orderId);

        if ($order === null) {
            throw new InvalidArgumentException('Order not found.');
        }

        if ($order->buyer_id !== $requesterId && $order->farmer_id !== $requesterId) {
            throw new RuntimeException('You do not have permission to view this order.');
        }

        return OrderResponseData::fromModel($order);
    }

    public function viewOrdersForUser(int $userId): array
    {
        $orders = Order::with(self::RELATIONS)
            ->where('buyer_id', $userId)
            ->orWhere('farmer_id', $userId)
            ->orderByDesc('created_at')
            ->get();

        return $orders->map(fn (Order $o) => OrderResponseData::fromModel($o))->all();
    }

    public function viewSalesHistory(int $farmerId): array
    {
        $orders = Order::with(self::RELATIONS)
            ->where('farmer_id', $farmerId)
            ->where('status', OrderStatus::Completed)
            ->orderByDesc('created_at')
            ->get();

        return $orders->map(fn (Order $o) => OrderResponseData::fromModel($o))->all();
    }

    public function previewFixedPricePurchase(array $items): CheckoutPreviewData
    {
        $quantities = $this->mergeQuantities($items);

        $listings = Listing::with('farmer')
            ->whereIn('id', array_keys($quantities))
            ->get()
            ->keyBy('id');

        [$lines] = $this->resolveFixedPriceLines($quantities, $listings);

        $previewItems = array_map(function (array $line) use ($listings) {
            $listing = $listings->get($line['listing_id']);

            return new CheckoutPreviewItemData(
                listingId: $line['listing_id'],
                cropType: $listing->crop_type,
                quantity: $line['qty'],
                unitPrice: $line['unit_price'],
                subTotal: $line['sub_total'],
                availableStock: $listing->qty,
            );
        }, $lines);

        return new CheckoutPreviewData(
            farmerName: $listings->first()->farmer->full_name,
            items: $previewItems,
            totalAmount: $this->sumLines($lines),
            currency: 'LKR',
        );
    }

    /**
     * Collapses the cart into [listing_id => total qty] so the same listing
     * can never produce two order_items rows.
     *
     * @param \App\Domains\Order\DTOs\CartItemData[] $items
     * @return array<int, int>
     */
    private function mergeQuantities(array $items): array
    {
        if ($items === []) {
            throw new InvalidArgumentException('An order must contain at least one item.');
        }

        $quantities = [];

        foreach ($items as $item) {
            if ($item->quantity <= 0) {
                throw new InvalidArgumentException('Quantity must be positive.');
            }

            $quantities[$item->listingId] = ($quantities[$item->listingId] ?? 0) + $item->quantity;
        }

        return $quantities;
    }

    /**
     * Validates every cart line against its listing and builds the order_items rows.
     * One order belongs to one farmer, so a cart mixing farmers is rejected.
     *
     * @param array<int, int> $quantities
     * @param Collection<int, Listing> $listings keyed by id
     * @return array{0: array<int, array{listing_id: int, qty: int, unit_price: float, sub_total: float}>, 1: int}
     */
    private function resolveFixedPriceLines(array $quantities, Collection $listings): array
    {
        $lines = [];

        foreach ($quantities as $listingId => $qty) {
            $listing = $listings->get($listingId);

            if ($listing === null) {
                throw new InvalidArgumentException("Listing {$listingId} not found.");
            }

            if ($listing->status !== ListingStatus::Active) {
                throw new RuntimeException("Listing {$listingId} is not currently active.");
            }

            if ($listing->selling_method !== SellingMethod::FixedPrice) {
                throw new InvalidArgumentException("Listing {$listingId} is not available for direct purchase.");
            }

            if ($qty > $listing->qty) {
                throw new RuntimeException("Insufficient stock for listing {$listingId}.");
            }

            $unitPrice = (float) $listing->price_per_unit;

            $lines[] = [
                'listing_id' => (int) $listingId,
                'qty' => $qty,
                'unit_price' => $unitPrice,
                'sub_total' => round($unitPrice * $qty, 2),
            ];
        }

        $farmerIds = $listings->pluck('farmer_id')->unique();

        if ($farmerIds->count() > 1) {
            throw new InvalidArgumentException('All items in one order must come from the same farmer.');
        }

        return [$lines, (int) $farmerIds->first()];
    }

    private function sumLines(array $lines): float
    {
        return round(array_sum(array_column($lines, 'sub_total')), 2);
    }

    // The SMS goes out after the transaction has committed, so a rolled-back
    // order never triggers an alert.
    private function finalizeNewOrder(Order $order, bool $notifyFarmer = true): OrderResponseData
    {
        $order->load(self::RELATIONS);

        if ($notifyFarmer) {
            $this->smsAlertService->sendNewOrderAlert($order->farmer, $order);
        }

        return OrderResponseData::fromModel($order);
    }
}
