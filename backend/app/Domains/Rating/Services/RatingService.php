<?php

namespace App\Domains\Rating\Services;

use App\Domains\Order\Models\Order;
use App\Domains\Rating\DTOs\RatingData;
use App\Domains\Rating\DTOs\RatingSummaryData;
use App\Domains\Rating\Events\RatingSubmitted;
use App\Domains\Rating\Models\Rating;
use App\Enums\OrderStatus;
use App\Enums\UserType;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class RatingService implements RatingServiceInterface
{
    public function submit(User $user, int $orderId, int $score, ?string $comment): RatingData
    {
        $order = $this->orderOf($user, $orderId);

        if ($order->status !== OrderStatus::Completed) {
            throw new RuntimeException('You can rate an order once it is completed.');
        }

        // buyer reviews farmer; farmer reviews buyer
        $rateeId = $user->id === $order->buyer_id ? $order->farmer_id : $order->buyer_id;

        if (Rating::where('order_id', $order->id)->where('rater_id', $user->id)->exists()) {
            throw new RuntimeException('You have already rated this order.');
        }

        try {
            $rating = Rating::create([
                'order_id' => $order->id,
                'rater_id' => $user->id,
                'ratee_id' => $rateeId,
                'score' => $score,
                'comment' => $comment !== null && trim($comment) !== '' ? trim($comment) : null,
            ]);
        } catch (QueryException $e) {
            if (($e->errorInfo[1] ?? null) !== 1062) {
                throw $e;
            }

            // two requests at once: the unique key (order_id, rater_id) stops the second
            throw new RuntimeException('You have already rated this order.');
        }

        RatingSubmitted::dispatch($rating);

        return RatingData::fromModel($rating->load('rater.buyerProfile', 'ratee.buyerProfile'));
    }

    public function forOrder(User $user, int $orderId): array
    {
        $order = $this->orderOf($user, $orderId);

        return Rating::with(['rater.buyerProfile', 'ratee.buyerProfile'])
            ->where('order_id', $order->id)
            ->orderBy('id')
            ->get()
            ->map(fn (Rating $r) => RatingData::fromModel($r))
            ->all();
    }

    public function received(User $user, int $page, int $perPage): array
    {
        return $this->reviewsOf($user->id, $page, $perPage) + ['summary' => $this->summary($user->id)];
    }

    public function given(User $user, int $page, int $perPage): array
    {
        $query = Rating::where('rater_id', $user->id);

        return $this->paginate($query, $page, $perPage);
    }

    public function reviewsFor(User $viewer, int $userId, int $page, int $perPage): array
    {
        $target = User::find($userId);

        if ($target === null || !in_array($target->role, [UserType::Farmer, UserType::Buyer], true)) {
            throw new InvalidArgumentException('User not found.');
        }

        // other buyers cant see another buyers ratings
        if ($target->role === UserType::Buyer
            && $viewer->id !== $target->id
            && !in_array($viewer->role, [UserType::Farmer, UserType::Admin], true)) {
            throw new AuthorizationException('You cannot view reviews of this user.');
        }

        return $this->reviewsOf($target->id, $page, $perPage) + ['summary' => $this->summary($target->id)];
    }

    public function summary(int $userId): RatingSummaryData
    {
        $perStar = Rating::where('ratee_id', $userId)
            ->select('score', DB::raw('count(*) as n'))
            ->groupBy('score')
            ->pluck('n', 'score');

        $distribution = [];
        $count = 0;
        $total = 0;

        foreach ([5, 4, 3, 2, 1] as $star) {
            $n = (int) ($perStar[$star] ?? 0);
            $distribution[$star] = $n;
            $count += $n;
            $total += $star * $n;
        }

        return new RatingSummaryData($count > 0 ? round($total / $count, 1) : null, $count, $distribution);
    }

    // helpers
    private function orderOf(User $user, int $orderId): Order
    {
        $order = Order::find($orderId);

        if ($order === null || ($order->buyer_id !== $user->id && $order->farmer_id !== $user->id)) {
            throw new InvalidArgumentException('Order not found.');
        }

        return $order;
    }

    private function reviewsOf(int $rateeId, int $page, int $perPage): array
    {
        return $this->paginate(Rating::where('ratee_id', $rateeId), $page, $perPage);
    }

    private function paginate($query, int $page, int $perPage): array
    {
        $total = (clone $query)->count();

        $ratings = $query
            ->with(['rater.buyerProfile', 'ratee.buyerProfile'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->forPage($page, $perPage)
            ->get();

        return [
            'data' => $ratings->map(fn (Rating $r) => RatingData::fromModel($r))->all(),
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
        ];
    }
}
