<?php

namespace App\Domains\Notification\Services;

use App\Domains\Listing\Models\Listing;
use App\Domains\Order\Models\Order;
use App\Models\User;

interface SmsAlertServiceInterface
{
    public function sendNewOrderAlert(User $farmer, Order $order): void;

    public function sendLowStockAlert(User $farmer, Listing $listing): void;

    // add later -- new bid sms alert
    public function sendNewBidAlert(User $farmer, string $buyerName, float $bidAmount, string $cropType): void;
}