<?php

namespace App\Domains\Order\Services;

use App\Domains\Order\DTOs\CartItemData;
use App\Domains\Order\DTOs\CreateBidOrderData;
use App\Domains\Order\DTOs\CreateFixedPriceOrderData;
use App\Domains\Order\DTOs\CheckoutPreviewData;
use App\Domains\Order\DTOs\OrderResponseData;
use App\Enums\OrderStatus;

interface OrderServiceInterface
{
    public function createFromFixedPricePurchase(CreateFixedPriceOrderData $data): OrderResponseData;

    public function createFromAcceptedBid(CreateBidOrderData $data): OrderResponseData;

    public function updateStatus(int $orderId, int $requesterId, OrderStatus $newStatus): OrderResponseData;

    /** @param CartItemData[] $items */
    public function previewFixedPricePurchase(array $items): CheckoutPreviewData;

    public function viewOrder(int $orderId, int $requesterId): OrderResponseData;

    /** @return OrderResponseData[] */
    public function viewOrdersForUser(int $userId): array;

    /** @return OrderResponseData[] */
    public function viewSalesHistory(int $farmerId): array;
}
