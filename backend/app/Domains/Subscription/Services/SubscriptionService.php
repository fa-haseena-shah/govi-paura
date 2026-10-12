<?php

namespace App\Domains\Subscription\Services;

use App\Domains\Payment\Models\Payment;
use App\Domains\Payment\Models\Receipt;
use App\Domains\Subscription\DTOs\SubscriptionData;
use App\Domains\Subscription\DTOs\SubscriptionPlanData;
use App\Domains\Subscription\DTOs\SubscriptionStatusData;
use App\Domains\Subscription\Events\SubscriptionExpired;
use App\Domains\Subscription\Events\SubscriptionExpiring;
use App\Domains\Subscription\Models\Subscription;
use App\Enums\PaymentStatus;
use App\Enums\SubscriptionStatus;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class SubscriptionService implements SubscriptionServiceInterface
{
    public function plans(): array
    {
        return collect(config('subscriptions.plans'))
            ->map(fn (array $plan, string $key) => new SubscriptionPlanData(
                key: $key,
                name: $plan['name'],
                months: (int) $plan['months'],
                fee: (float) $plan['fee'],
                currency: config('subscriptions.currency', 'LKR'),
                description: $plan['description'] ?? '',
            ))
            ->values()
            ->all();
    }

    public function subscribe(User $rider, string $plan): SubscriptionData
    {
        $this->assertRider($rider);
        $this->syncStatuses($rider->id);

        $definition = $this->plan($plan);
        $this->assertNothingPending($rider->id);

        $covered = Subscription::where('rider_id', $rider->id)
            ->whereIn('status', [SubscriptionStatus::Active->value, SubscriptionStatus::Scheduled->value])
            ->exists();

        if ($covered) {
            throw new RuntimeException('You already have an active subscription. Renew it to extend it.');
        }

        return $this->createPending($rider, $plan, $definition);
    }

    public function renew(User $rider, ?string $plan): SubscriptionData
    {
        $this->assertRider($rider);
        $this->syncStatuses($rider->id);

        $latest = Subscription::where('rider_id', $rider->id)
            ->whereIn('status', [SubscriptionStatus::Active->value, SubscriptionStatus::Scheduled->value, SubscriptionStatus::Expired->value])
            ->orderByDesc('end_date')
            ->first();

        if ($latest === null) {
            throw new RuntimeException('You have no subscription to renew. Subscribe to a plan first.');
        }

        $this->assertNothingPending($rider->id);

        if ($latest->status === SubscriptionStatus::Scheduled) {
            throw new RuntimeException("Your subscription is already renewed until {$latest->end_date->format('d M Y')}.");
        }

        $plan ??= $latest->type;

        return $this->createPending($rider, $plan, $this->plan($plan));
    }

    public function status(User $rider): SubscriptionStatusData
    {
        $this->assertRider($rider);
        $this->syncStatuses($rider->id);

        $all = Subscription::where('rider_id', $rider->id)->orderByDesc('id')->get();
        $data = collect($this->present($all))->keyBy('id');
        $find = fn (SubscriptionStatus $status) => $all->first(fn (Subscription $s) => $s->status === $status);
        $dto = fn (?Subscription $s) => $s === null ? null : $data->get($s->id);

        $current = $find(SubscriptionStatus::Active);
        $upcoming = $find(SubscriptionStatus::Scheduled);
        $pending = $find(SubscriptionStatus::Pending);
        $expired = $all->filter(fn (Subscription $s) => $s->status === SubscriptionStatus::Expired)->sortByDesc('end_date')->first();

        $hasHistory = $current !== null || $upcoming !== null || $expired !== null;
        $state = $current !== null ? 'active' : ($hasHistory ? 'expired' : 'none');
        $coveredUntil = ($upcoming ?? $current)?->end_date;

        return new SubscriptionStatusData(
            state: $state,
            current: $dto($current),
            upcoming: $dto($upcoming),
            pending: $dto($pending),
            coveredUntil: $coveredUntil?->toDateString(),
            expiredOn: $state === 'expired' ? $expired?->end_date?->toDateString() : null,
            daysRemaining: $dto($current)?->daysRemaining,
            canSubscribe: !$hasHistory && $pending === null,
            canRenew: $hasHistory && $pending === null && $upcoming === null,
        );
    }

    public function history(User $rider): array
    {
        $this->assertRider($rider);
        $this->syncStatuses($rider->id);

        return $this->present(Subscription::where('rider_id', $rider->id)->orderByDesc('id')->get());
    }

    public function cancel(User $rider, int $subscriptionId): SubscriptionData
    {
        $this->assertRider($rider);

        $subscription = Subscription::where('rider_id', $rider->id)->find($subscriptionId);

        if ($subscription === null) {
            throw new InvalidArgumentException('Subscription not found.');
        }

        if ($subscription->status !== SubscriptionStatus::Pending) {
            throw new RuntimeException('Only a subscription that has not been paid yet can be cancelled.');
        }

        $subscription->update(['status' => SubscriptionStatus::Cancelled]);

        return $this->present(collect([$subscription->fresh()]))[0];
    }

    public function activatePaid(int $subscriptionId): SubscriptionData
    {
        $subscription = DB::transaction(function () use ($subscriptionId) {
            $subscription = Subscription::lockForUpdate()->find($subscriptionId);

            if ($subscription === null) {
                throw new InvalidArgumentException('Subscription not found.');
            }

            if ($subscription->payment_status === PaymentStatus::Paid) {
                throw new RuntimeException('This subscription has already been paid.');
            }

            $today = Carbon::today();

            // continue from the end of the paid period that is still running, otherwise start today
            $latestEnd = Subscription::where('rider_id', $subscription->rider_id)
                ->whereIn('status', [SubscriptionStatus::Active->value, SubscriptionStatus::Scheduled->value])
                ->where('id', '!=', $subscription->id)
                ->max('end_date');

            $start = $latestEnd !== null && Carbon::parse($latestEnd)->gte($today)
                ? Carbon::parse($latestEnd)->addDay()
                : $today->copy();

            // one month period calculation
            $end = $start->copy()->addMonthsNoOverflow($subscription->period_months)->subDay();

            $subscription->update([
                'payment_status' => PaymentStatus::Paid,
                'status' => $start->gt($today) ? SubscriptionStatus::Scheduled : SubscriptionStatus::Active,
                'start_date' => $start,
                'end_date' => $end,
                'paid_at' => now(),
            ]);

            return $subscription;
        });

        return $this->present(collect([$subscription->fresh()]))[0];
    }

    public function hasActiveSubscription(int $riderId): bool
    {
        $this->syncStatuses($riderId);

        return Subscription::where('rider_id', $riderId)->where('status', SubscriptionStatus::Active->value)->exists();
    }

    public function syncStatuses(?int $riderId = null): array
    {
        $today = Carbon::today()->toDateString();
        $scope = fn () => Subscription::query()->when($riderId !== null, fn ($q) => $q->where('rider_id', $riderId));

        $expired = $scope()->where('status', SubscriptionStatus::Active->value)->whereDate('end_date', '<', $today)->get();
        $activated = $scope()->where('status', SubscriptionStatus::Scheduled->value)->whereDate('start_date', '<=', $today)->get();

        if ($expired->isNotEmpty()) {
            Subscription::whereIn('id', $expired->pluck('id'))->update(['status' => SubscriptionStatus::Expired->value, 'updated_at' => now()]);
            // rider who opens the app first still gets the notice
            $expired->each(fn (Subscription $s) => SubscriptionExpired::dispatch($s->fresh()));
        }

        if ($activated->isNotEmpty()) {
            Subscription::whereIn('id', $activated->pluck('id'))->update(['status' => SubscriptionStatus::Active->value, 'updated_at' => now()]);
        }

        return ['activated' => $activated->count(), 'expired' => $expired->count()];
    }

    public function sendExpiryReminders(int $days): int
    {
        $ending = Subscription::where('status', SubscriptionStatus::Active->value)
            ->whereDate('end_date', Carbon::today()->addDays($days)->toDateString())
            ->get();

        $sent = 0;

        foreach ($ending as $subscription) {
            $renewed = Subscription::where('rider_id', $subscription->rider_id)
                ->whereIn('status', [SubscriptionStatus::Scheduled->value, SubscriptionStatus::Pending->value])
                ->exists();

            if (!$renewed) {
                SubscriptionExpiring::dispatch($subscription);
                $sent++;
            }
        }

        return $sent;
    }

    // helpers
    private function assertRider(User $user): void
    {
        if (!$user->isRider()) {
            throw new InvalidArgumentException('Only riders have subscriptions.');
        }
    }

    private function plan(string $key): array
    {
        $plan = config("subscriptions.plans.{$key}");

        if ($plan === null) {
            throw new InvalidArgumentException('Unknown plan.');
        }

        return $plan;
    }

    private function assertNothingPending(int $riderId): void
    {
        $pending = Subscription::where('rider_id', $riderId)->where('status', SubscriptionStatus::Pending->value)->exists();

        if ($pending) {
            throw new RuntimeException('You already have a subscription waiting for payment. Pay for it or cancel it first.');
        }
    }

    private function createPending(User $rider, string $plan, array $definition): SubscriptionData
    {
        $subscription = Subscription::create([
            'rider_id' => $rider->id,
            'type' => $plan,
            'status' => SubscriptionStatus::Pending,
            'fee' => $definition['fee'],
            'period_months' => $definition['months'],
            'payment_status' => PaymentStatus::Unpaid,
        ]);

        return $this->present(collect([$subscription]))[0];
    }

    /**
     * @param Collection<int, Subscription> $subscriptions
     * @return SubscriptionData[]
     */
    private function present(Collection $subscriptions): array
    {
        $ids = $subscriptions->pluck('id');

        $paymentToSubscription = Payment::where('payable_type', 'subscription')->whereIn('payable_id', $ids)->pluck('payable_id', 'id');
        $receipts = Receipt::whereIn('payment_id', $paymentToSubscription->keys())->get();

        // receipt of each subscription's payment
        $receiptBySubscription = [];
        foreach ($receipts as $receipt) {
            $receiptBySubscription[$paymentToSubscription[$receipt->payment_id]] = $receipt->receipt_number;
        }

        return $subscriptions
            ->map(fn (Subscription $s) => SubscriptionData::fromModel($s, $receiptBySubscription[$s->id] ?? null))
            ->values()
            ->all();
    }
}
