<?php
namespace App\Domains\Payment\Services;

use App\Domains\Payment\DTOs\PayOrderData;
use App\Domains\Payment\DTOs\PaymentResponseData;

interface PaymentServiceInterface
{
    public function payForOrder(PayOrderData $data): PaymentResponseData;

    // for cash on delivery
    public function confirmCashCollected(int $orderId, int $confirmedByUserId): PaymentResponseData;

    public function payForSubscription(int $subscriptionId, int $riderId, string $method): PaymentResponseData;
}