<?php

namespace App\Domains\Rating\Services;

use App\Domains\Rating\DTOs\RatingData;
use App\Domains\Rating\DTOs\RatingSummaryData;
use App\Models\User;

interface RatingServiceInterface
{
    /** 9.1 and 9.2: review the other party of a completed order (a score from 1 to 5 and an optional comment). */
    public function submit(User $user, int $orderId, int $score, ?string $comment): RatingData;

    /** The reviews on one order (at most two: the buyer's and the farmer's). Only the order's own parties can see them. @return RatingData[] */
    public function forOrder(User $user, int $orderId): array;

    /**
     * 9.3: reviews the user has received, with their overall rating.
     * @return array{data: RatingData[], total: int, page: int, perPage: int, summary: RatingSummaryData}
     */
    public function received(User $user, int $page, int $perPage): array;

    /** Reviews the user has written. @return array{data: RatingData[], total: int, page: int, perPage: int} */
    public function given(User $user, int $page, int $perPage): array;

    /**
     * 9.3 and 9.4: another farmer's or buyer's reviews and overall rating, for their profile.
     * A farmer's reviews can be seen by any signed-in user; a buyer's only by farmers, admins and the buyer.
     * @return array{data: RatingData[], total: int, page: int, perPage: int, summary: RatingSummaryData}
     */
    public function reviewsFor(User $viewer, int $userId, int $page, int $perPage): array;

    /** 9.4: average, count and the split per star. */
    public function summary(int $userId): RatingSummaryData;
}
