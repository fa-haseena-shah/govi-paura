<?php

namespace App\Domains\PriceHistory\Services;

use App\Domains\Order\Models\Order;
use App\Domains\PriceHistory\DTOs\PriceHistoryFilterData;
use App\Domains\PriceHistory\Models\PriceRecord;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PriceHistoryService implements PriceHistoryServiceInterface
{
    // trends cover the last 6 months unless the user picks their own dates
    private const DEFAULT_TREND_DAYS = 180;

    public function recordForOrder(Order $order, ?CarbonInterface $recordedAt = null): int
    {
        // a transaction counts once it is paid, or once the order has been completed
        if ($order->payment_status !== PaymentStatus::Paid && $order->status !== OrderStatus::Completed) {
            return 0;
        }

        $order->loadMissing('items.listing');

        $count = 0;

        foreach ($order->items as $item) {
            if ($item->listing === null) {
                continue;
            }

            PriceRecord::updateOrCreate(
                ['order_item_id' => $item->id],
                [
                    'order_id' => $order->id,
                    'listing_id' => $item->listing_id,
                    'crop_type' => $this->normalise($item->listing->crop_type),
                    'region' => $this->normalise($item->listing->location),
                    'unit_price' => $item->unit_price,
                    'qty' => $item->qty,
                    'total_amount' => $item->sub_total,
                    'source' => $order->source->value,
                    'recorded_at' => $recordedAt ?? now(),
                ],
            );
            $count++;
        }

        return $count;
    }

    public function history(PriceHistoryFilterData $filter): array
    {
        $query = $this->filtered($filter);
        $total = (clone $query)->count();

        $rows = $query
            ->orderByDesc('recorded_at')
            ->orderByDesc('id')
            ->forPage($filter->page, $filter->perPage)
            ->get()
            ->map(fn (PriceRecord $r) => [
                'id' => $r->id,
                'cropType' => $r->crop_type,
                'region' => $r->region,
                'unitPrice' => (float) $r->unit_price,
                'qty' => $r->qty,
                'totalAmount' => (float) $r->total_amount,
                'source' => $r->source,
                'recordedAt' => $r->recorded_at?->toIso8601String(),
            ])
            ->all();

        return ['data' => $rows, 'total' => $total, 'page' => $filter->page, 'perPage' => $filter->perPage];
    }

    public function trends(PriceHistoryFilterData $filter): array
    {
        if ($filter->from === null && $filter->to === null) {
            $filter = new PriceHistoryFilterData(
                $filter->cropType, $filter->region,
                CarbonImmutable::now()->subDays(self::DEFAULT_TREND_DAYS)->startOfDay(), null,
                $filter->interval, $filter->page, $filter->perPage,
            );
        }

        $records = $this->filtered($filter)
            ->orderBy('recorded_at')
            ->get(['crop_type', 'region', 'unit_price', 'qty', 'recorded_at']);

        $series = $records
            ->groupBy(fn (PriceRecord $r) => $this->bucketKey($r->recorded_at, $filter->interval))
            ->map(fn (Collection $group, string $period) => ['period' => $period] + $this->aggregate($group))
            ->sortKeys()
            ->values();

        $first = $series->first();
        $last = $series->last();
        $changePct = ($first && $last && $first['avgPrice'] > 0 && $series->count() > 1)
            ? round((($last['avgPrice'] - $first['avgPrice']) / $first['avgPrice']) * 100, 1)
            : null;

        return [
            'summary' => $this->aggregate($records) + [
                'latestPrice' => $records->isEmpty() ? null : (float) $records->last()->unit_price,
                'changePct' => $changePct,
                'from' => $filter->from?->toDateString(),
                'to' => $filter->to?->toDateString(),
                'interval' => $filter->interval,
            ],
            'series' => $series->all(),
            'byCrop' => $this->breakdown($records, 'crop_type'),
            'byRegion' => $this->breakdown($records, 'region'),
        ];
    }

    public function filterOptions(): array
    {
        return [
            'crops' => PriceRecord::query()->distinct()->orderBy('crop_type')->pluck('crop_type')->all(),
            'regions' => PriceRecord::query()->distinct()->orderBy('region')->pluck('region')->all(),
        ];
    }

    private function filtered(PriceHistoryFilterData $filter): Builder
    {
        return PriceRecord::query()
            ->when($filter->cropType, fn (Builder $q, string $crop) => $q->whereRaw('LOWER(crop_type) = ?', [Str::lower($crop)]))
            ->when($filter->region, fn (Builder $q, string $region) => $q->where('region', 'like', '%' . $region . '%'))
            ->when($filter->from, fn (Builder $q, $from) => $q->where('recorded_at', '>=', $from))
            ->when($filter->to, fn (Builder $q, $to) => $q->where('recorded_at', '<=', $to));
    }

    /** Price statistics for a group of records. avgPrice is weighted by quantity sold. */
    private function aggregate(Collection $records): array
    {
        $qty = (int) $records->sum('qty');

        return [
            'transactions' => $records->count(),
            'totalQty' => $qty,
            'avgPrice' => $qty > 0 ? round($records->sum(fn ($r) => (float) $r->unit_price * $r->qty) / $qty, 2) : 0.0,
            'minPrice' => $records->isEmpty() ? 0.0 : (float) $records->min('unit_price'),
            'maxPrice' => $records->isEmpty() ? 0.0 : (float) $records->max('unit_price'),
        ];
    }

    private function breakdown(Collection $records, string $column): array
    {
        return $records
            ->groupBy($column)
            ->map(fn (Collection $group, string $name) => ['name' => $name] + $this->aggregate($group))
            ->sortByDesc('transactions')
            ->values()
            ->all();
    }

    private function bucketKey($date, string $interval): string
    {
        $date = CarbonImmutable::parse($date);

        return match ($interval) {
            'day' => $date->toDateString(),
            'month' => $date->format('Y-m'),
            default => $date->startOfWeek()->toDateString(), // week starting Monday
        };
    }

    // "carrots " and "Carrots" must count as the same crop, same for regions
    private function normalise(string $value): string
    {
        return Str::title(trim($value));
    }
}
