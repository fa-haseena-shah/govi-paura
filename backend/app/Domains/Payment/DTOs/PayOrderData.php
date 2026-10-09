<?php
namespace App\Domains\Payment\DTOs;

use App\Enums\PaymentMethod;

final readonly class PayOrderData
{
    public function __construct(
        public int $orderId,
        public int $buyerId,
        public PaymentMethod $method,
    ) {}

    public static function fromArray(array $data, int $orderId, int $buyerId): self
    {
        return new self(
            orderId: $orderId,
            buyerId: $buyerId,
            method: PaymentMethod::from($data['method']),
        );
    }
}