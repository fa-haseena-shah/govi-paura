<?php

namespace App\Domains\Delivery\Http\Controllers;

use App\Domains\Delivery\Exceptions\DeliveryAccessException;
use App\Domains\Delivery\Http\Requests\UpdateDeliveryStatusRequest;
use App\Domains\Delivery\Services\DeliveryServiceInterface;
use App\Enums\DeliveryStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use InvalidArgumentException;
use RuntimeException;

class DeliveryController extends Controller
{
    public function __construct(private readonly DeliveryServiceInterface $deliveries) {}

    /* ---------- riders ---------- */

    // 8.1 open deliveries in the rider's region, most urgent first
    public function opportunities(Request $request): JsonResponse
    {
        $validated = $request->validate(['region' => ['nullable', 'string', 'max:255']]);
        [$page, $perPage] = $this->paging($request);

        return $this->respondPage(fn () => $this->deliveries->opportunities($request->user(), $validated['region'] ?? null, $page, $perPage));
    }

    // 8.1 take an open delivery
    public function claim(Request $request, int $delivery): JsonResponse
    {
        return $this->respond(fn () => $this->deliveries->claim($request->user(), $delivery));
    }

    // hand it back before pick-up
    public function release(Request $request, int $delivery): JsonResponse
    {
        $validated = $request->validate(['reason' => ['nullable', 'string', 'max:300']]);

        return $this->respond(fn () => $this->deliveries->release($request->user(), $delivery, $validated['reason'] ?? null));
    }

    // 8.2 deliveries assigned to me that are not finished
    public function assigned(Request $request): JsonResponse
    {
        return $this->respond(fn () => $this->deliveries->assigned($request->user()));
    }

    // 8.5 move a delivery to its next stage
    public function updateStatus(UpdateDeliveryStatusRequest $request, int $delivery): JsonResponse
    {
        $validated = $request->validated();

        return $this->respond(fn () => $this->deliveries->updateStatus(
            $request->user(),
            $delivery,
            DeliveryStatus::from($validated['status']),
            $validated['note'] ?? null,
        ));
    }

    // 8.7 completed deliveries
    public function history(Request $request): JsonResponse
    {
        [$page, $perPage] = $this->paging($request);

        return $this->respondPage(fn () => $this->deliveries->history($request->user(), $page, $perPage));
    }

    /* ---------- riders, farmers, buyers, admins ---------- */

    // 8.4 details (and 8.3 pins inside pickup / dropoff)
    public function show(Request $request, int $delivery): JsonResponse
    {
        return $this->respond(fn () => $this->deliveries->show($request->user(), $delivery));
    }

    // 8.3 Leaflet payload
    public function map(Request $request, int $delivery): JsonResponse
    {
        return $this->respond(fn () => $this->deliveries->map($request->user(), $delivery));
    }

    /* ---------- farmers and buyers ---------- */

    // 8.6 track the delivery of one order
    public function forOrder(Request $request, int $order): JsonResponse
    {
        return $this->respond(fn () => $this->deliveries->trackOrder($request->user(), $order));
    }

    // 8.6 track all of my deliveries
    public function tracking(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isFarmer() && !$user->isBuyer()) {
            return $this->error('Only farmers and buyers can track deliveries here.', 403);
        }

        return $this->respond(fn () => $this->deliveries->trackingFor($user));
    }

    /* ---------- admin ---------- */

    public function adminIndex(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isAdmin()) {
            return $this->error('Only admins can view all deliveries.', 403);
        }

        $validated = $request->validate([
            'status' => ['nullable', Rule::enum(DeliveryStatus::class)],
            'at_risk' => ['nullable', 'boolean'],
        ]);

        [$page, $perPage] = $this->paging($request);

        return $this->respondPage(fn () => $this->deliveries->adminList(
            $user,
            $validated['status'] ?? null,
            (bool) ($validated['at_risk'] ?? false),
            $page,
            $perPage,
        ));
    }

    /* ---------- helpers ---------- */

    private function respond(callable $action): JsonResponse
    {
        try {
            return response()->json(['status' => 'success', 'data' => $action()]);
        } catch (\Throwable $e) {
            return $this->failure($e);
        }
    }

    // paginated results: items in data, the paging numbers in meta
    private function respondPage(callable $action): JsonResponse
    {
        try {
            $result = $action();
            $data = $result['data'];
            unset($result['data']);

            return response()->json(['status' => 'success', 'data' => $data, 'meta' => $result]);
        } catch (\Throwable $e) {
            return $this->failure($e);
        }
    }

    private function failure(\Throwable $e): JsonResponse
    {
        // the order matters: DeliveryAccessException is a RuntimeException
        return match (true) {
            $e instanceof DeliveryAccessException => $this->error($e->getMessage(), 403),
            $e instanceof InvalidArgumentException => $this->error($e->getMessage(), 404),
            $e instanceof RuntimeException => $this->error($e->getMessage(), 409),
            default => throw $e,
        };
    }

    private function error(string $message, int $status): JsonResponse
    {
        return response()->json(['status' => 'error', 'message' => $message], $status);
    }

    /** @return array{0: int, 1: int} */
    private function paging(Request $request): array
    {
        $page = max(1, (int) $request->query('page', 1));
        $perPage = (int) $request->query('per_page', config('delivery.default_per_page'));
        $perPage = min(max(1, $perPage), (int) config('delivery.max_per_page'));

        return [$page, $perPage];
    }
}
