<?php

namespace App\Domains\PriceHistory\DTOs;

use Carbon\CarbonImmutable;

final readonly class PriceHistoryFilterData
{
    public const INTERVALS = ['day', 'week', 'month'];

    public function __construct(
        public ?string $cropType = null,
        public ?string $region = null,
        public ?CarbonImmutable $from = null,
        public ?CarbonImmutable $to = null,
        public string $interval = 'week',
        public int $page = 1,
        public int $perPage = 20,
    ) {}

    public static function fromArray(array $data): self
    {
        $interval = $data['interval'] ?? 'week';

        return new self(
            cropType: filled($data['crop_type'] ?? null) ? trim($data['crop_type']) : null,
            region: filled($data['region'] ?? null) ? trim($data['region']) : null,
            from: filled($data['from'] ?? null) ? CarbonImmutable::parse($data['from'])->startOfDay() : null,
            to: filled($data['to'] ?? null) ? CarbonImmutable::parse($data['to'])->endOfDay() : null,
            interval: in_array($interval, self::INTERVALS, true) ? $interval : 'week',
            page: isset($data['page']) ? max(1, (int) $data['page']) : 1,
            perPage: isset($data['per_page']) ? min(100, max(1, (int) $data['per_page'])) : 20,
        );
    }
}
