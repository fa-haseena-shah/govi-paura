<?php

namespace App\Domains\Notification\Services;

use App\Domains\Listing\Models\Listing;
use App\Domains\Order\Models\Order;
use App\Models\User;

interface SmsAlertServiceInterface
{
    public function sendNewOrderAlert(User $farmer, Order $order): void;

    public function sendLowStockAlert(User $farmer, Listing $listing): void;

    public function sendNewBidAlert(User $farmer, string $buyerName, float $bidAmount, string $cropType): void;

    public function sendDeliveryAtRiskAlert(User $admin, int $orderId, string $state, int $minutesLeft): void;

    public function sendDeliveryOverdueAlert(User $admin, int $orderId, int $slaHours, string $state): void;
}
