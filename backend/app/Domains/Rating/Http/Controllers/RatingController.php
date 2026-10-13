<?php

namespace App\Domains\Rating\Http\Controllers;

use App\Domains\Rating\Http\Requests\SubmitRatingRequest;
use App\Domains\Rating\Services\RatingServiceInterface;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use RuntimeException;

class RatingController extends Controller
{
    public function __construct(private readonly RatingServiceInterface $ratings) {}

    public function store(SubmitRatingRequest $request, int $order): JsonResponse
    {
        return $this->partiesOnly($request, fn () => $this->ratings->submit(
            $request->user(), $order, (int) $request->validated('score'), $request->validated('comment'),
        ), 201);
    }

    public function forOrder(Request $request, int $order): JsonResponse
    {
        return $this->partiesOnly($request, fn () => $this->ratings->forOrder($request->user(), $order));
    }

    public function received(Request $request): JsonResponse
    {
        return $this->partiesOnly($request, fn () => $this->ratings->received($request->user(), ...$this->paging($request)), flat: true);
    }

    public function given(Request $request): JsonResponse
    {
        return $this->partiesOnly($request, fn () => $this->ratings->given($request->user(), ...$this->paging($request)), flat: true);
    }

    public function forUser(Request $request, int $user): JsonResponse
    {
        return $this->partiesOnly($request, fn () => $this->ratings->reviewsFor($request->user(), $user, ...$this->paging($request)), flat: true, allowAdmin: true);
    }

    private function paging(Request $request): array
    {
        return [max(1, (int) $request->query('page', 1)), min(50, max(1, (int) $request->query('per_page', 10)))];
    }

    private function partiesOnly(Request $request, callable $action, int $successCode = 200, bool $flat = false, bool $allowAdmin = false): JsonResponse
    {
        $user = $request->user();

        if (!($user->isFarmer() || $user->isBuyer() || ($allowAdmin && $user->isAdmin()))) {
            return response()->json(['status' => 'error', 'message' => 'Only farmers and buyers use ratings.'], 403);
        }

        try {
            $result = $action();

            return response()->json($flat ? ['status' => 'success', ...$result] : ['status' => 'success', 'data' => $result], $successCode);
        } catch (InvalidArgumentException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 404);
        } catch (AuthorizationException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 403);
        } catch (RuntimeException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 409);
        }
    }
}
