<?php

namespace App\Domains\Rating\DTOs;

use App\Domains\Rating\Models\Rating;
use App\Models\User;

final readonly class RatingData
{
    public function __construct(
        public int $id,
        public int $orderId,
        public int $raterId,
        public string $raterName,
        public string $raterRole,
        public int $rateeId,
        public string $rateeName,
        public int $score,
        public ?string $comment,
        public string $createdAt,
    ) {}

    public static function fromModel(Rating $rating): self
    {
        return new self(
            id: $rating->id,
            orderId: $rating->order_id,
            raterId: $rating->rater_id,
            raterName: self::displayName($rating->rater),
            raterRole: $rating->rater->role->value,
            rateeId: $rating->ratee_id,
            rateeName: self::displayName($rating->ratee),
            score: $rating->score,
            comment: $rating->comment,
            createdAt: $rating->created_at->toIso8601String(),
        );
    }

    private static function displayName(User $user): string
    {
        $user->loadMissing('buyerProfile');

        return $user->buyerProfile?->business_name ?? $user->full_name;
    }
}
