<?php

namespace App\Domains\PriceHistory\Services;

use App\Domains\Order\Models\Order;
use App\Domains\PriceHistory\DTOs\PriceHistoryFilterData;

interface PriceHistoryServiceInterface
{
    public function recordForOrder(Order $order, ?\Carbon\CarbonInterface $recordedAt = null): int;

    /** @return array{data: array, total: int, page: int, perPage: int} */
    public function history(PriceHistoryFilterData $filter): array;

    /** @return array{summary: array, series: array, byCrop: array, byRegion: array} */
    public function trends(PriceHistoryFilterData $filter): array;

    /** @return array{crops: string[], regions: string[]} */
    public function filterOptions(): array;
}
