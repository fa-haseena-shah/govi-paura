<?php

namespace App\Domains\Rating\Services;

use App\Domains\Rating\DTOs\RatingData;
use App\Domains\Rating\DTOs\RatingSummaryData;
use App\Models\User;

interface RatingServiceInterface
{
    public function submit(User $user, int $orderId, int $score, ?string $comment): RatingData;

    public function forOrder(User $user, int $orderId): array;

    /**
     * @return array{data: RatingData[], total: int, page: int, perPage: int, summary: RatingSummaryData}
     */
    public function received(User $user, int $page, int $perPage): array;

    /** @return array{data: RatingData[], total: int, page: int, perPage: int} */
    public function given(User $user, int $page, int $perPage): array;

    /**
     * @return array{data: RatingData[], total: int, page: int, perPage: int, summary: RatingSummaryData}
     */
    public function reviewsFor(User $viewer, int $userId, int $page, int $perPage): array;

    public function summary(int $userId): RatingSummaryData;
}
