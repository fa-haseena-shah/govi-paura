<?php

namespace App\Domains\Subscription\DTOs;

use App\Domains\Subscription\Models\Subscription;
use App\Enums\SubscriptionStatus;
use Illuminate\Support\Carbon;

final readonly class SubscriptionData
{
    public function __construct(
        public int $id,
        public string $plan,
        public string $planName,
        public string $status,
        public string $paymentStatus,
        public float $fee,
        public string $currency,
        public int $periodMonths,
        public ?string $startDate,
        public ?string $endDate,
        public ?int $daysRemaining, // while subscription is active
        public ?string $receiptNumber,
        public ?string $paidAt,
        public string $createdAt,
    ) {}

    public static function fromModel(Subscription $s, ?string $receiptNumber = null): self
    {
        return new self(
            id: $s->id,
            plan: $s->type,
            planName: config("subscriptions.plans.{$s->type}.name", ucfirst($s->type) . ' plan'),
            status: $s->status->value,
            paymentStatus: $s->payment_status->value,
            fee: (float) $s->fee,
            currency: config('subscriptions.currency', 'LKR'),
            periodMonths: (int) $s->period_months,
            startDate: $s->start_date?->toDateString(),
            endDate: $s->end_date?->toDateString(),
            daysRemaining: $s->status === SubscriptionStatus::Active && $s->end_date !== null
                ? max(0, (int) Carbon::today()->diffInDays($s->end_date, false))
                : null,
            receiptNumber: $receiptNumber,
            paidAt: $s->paid_at?->toIso8601String(),
            createdAt: $s->created_at->toIso8601String(),
        );
    }
}
