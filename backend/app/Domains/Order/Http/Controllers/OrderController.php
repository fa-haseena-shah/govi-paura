<?php

namespace App\Domains\Order\Http\Controllers;

use App\Domains\Order\DTOs\CartItemData;
use App\Domains\Order\DTOs\CreateFixedPriceOrderData;
use App\Domains\Order\Http\Requests\CreateOrderRequest;
use App\Domains\Order\Services\OrderServiceInterface;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use RuntimeException;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderServiceInterface $orderService,
    ) {}

    public function store(CreateOrderRequest $request): JsonResponse
    {
        if (!$request->user()->isBuyer()) {
            return response()->json(['status' => 'error', 'message' => 'Only buyers can place orders.'], 403);
        }

        try {
            $data = CreateFixedPriceOrderData::fromArray($request->validated(), $request->user()->id);
            $result = $this->orderService->createFromFixedPricePurchase($data);

            return response()->json(['status' => 'success', 'data' => $result], 201);
        } catch (InvalidArgumentException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
        } catch (RuntimeException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 409);
        }
    }

    public function show(Request $request, int $order): JsonResponse
    {
        try {
            $result = $this->orderService->viewOrder($order, $request->user()->id);
            return response()->json(['status' => 'success', 'data' => $result]);
        } catch (InvalidArgumentException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 404);
        } catch (RuntimeException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 403);
        }
    }

    public function index(Request $request): JsonResponse
    {
        $result = $this->orderService->viewOrdersForUser($request->user()->id);
        return response()->json(['status' => 'success', 'data' => $result]);
    }

    public function salesHistory(Request $request): JsonResponse
    {
        if (!$request->user()->isFarmer()) {
            return response()->json(['status' => 'error', 'message' => 'Only farmers can view sales history.'], 403);
        }

        $result = $this->orderService->viewSalesHistory($request->user()->id);
        return response()->json(['status' => 'success', 'data' => $result]);
    }

    public function updateStatus(Request $request, int $order): JsonResponse
    {
        $validated = $request->validate(['status' => ['required', 'string']]);

        try {
            $newStatus = OrderStatus::from($validated['status']);
            $result = $this->orderService->updateStatus($order, $request->user()->id, $newStatus);
            return response()->json(['status' => 'success', 'data' => $result]);
        } catch (\ValueError) {
            return response()->json(['status' => 'error', 'message' => 'Invalid status value.'], 400);
        } catch (InvalidArgumentException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
        } catch (RuntimeException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 403);
        }
    }

    public function preview(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.listing_id' => ['required', 'integer', 'distinct', 'exists:listings,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $result = $this->orderService->previewFixedPricePurchase(
                CartItemData::manyFromArray($validated['items']),
            );

            return response()->json(['status' => 'success', 'data' => $result]);
        } catch (InvalidArgumentException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
        } catch (RuntimeException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 409);
        }
    }
}
