<?php
namespace App\Domains\Payment\DTOs;

use App\Domains\Payment\Models\Payment;
use App\Domains\Payment\Models\Receipt;
use App\Enums\PaymentTransactionStatus;

final readonly class PaymentResponseData
{
    public function __construct(
        public int $paymentId,
        public string $reference,
        public float $amount,
        public string $currency,
        public PaymentTransactionStatus $transactionStatus,
        public string $method,
        public ?string $receiptNumber = null,
        public ?string $issuedAt = null,
    ) {}

    public static function fromModels(Payment $payment, ?Receipt $receipt = null): self
    {
        return new self(
            paymentId: $payment->id,
            reference: $payment->reference,
            amount: (float) $payment->amount,
            currency: $payment->currency,
            transactionStatus: $payment->status,
            method: $payment->method->value,
            receiptNumber: $receipt?->receipt_number,
            issuedAt: $receipt?->issued_at->toIso8601String(),
        );
    }
}