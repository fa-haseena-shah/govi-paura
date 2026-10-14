<?php

namespace App\Domains\Rating\DTOs;

use App\Models\User;

final readonly class RatingBadgeData
{
    public function __construct(
        public ?float $average,   // null until the first review
        public int $count,
    ) {}

    public static function fromUser(?User $user): self
    {
        $count = (int) ($user?->ratings_received_count ?? 0);
        $average = $user?->ratings_received_avg_score;

        return new self($count > 0 && $average !== null ? round((float) $average, 1) : null, $count);
    }
}
