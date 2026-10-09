<?php

namespace App\Domains\Payment\Http\Controllers;

use App\Domains\Payment\DTOs\PayOrderData;
use App\Domains\Payment\Http\Requests\PayOrderRequest;
use App\Domains\Payment\Services\PaymentServiceInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use RuntimeException;

class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentServiceInterface $paymentService,
    ) {}

    public function initiateForOrder(Request $request, int $order): JsonResponse
    {
        try {
            $checkoutData = $this->paymentService->initiateForOrder($order, $request->user()->id);
            return response()->json(['status' => 'success', 'data' => $checkoutData]);
        } catch (InvalidArgumentException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 404);
        } catch (RuntimeException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 409);
        }
    }

    public function payForOrder(PayOrderRequest $request, int $order): JsonResponse
    {
        try {
            $data = PayOrderData::fromArray($request->validated(), $order, $request->user()->id);
            $result = $this->paymentService->payForOrder($data);

            return response()->json(['status' => 'success', 'data' => $result], 201);
        } catch (InvalidArgumentException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 404);
        } catch (RuntimeException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 409);
        }
    }

    public function confirmCashCollected(Request $request, int $order): JsonResponse
    {
        try {
            $result = $this->paymentService->confirmCashCollected($order, $request->user()->id);
            return response()->json(['status' => 'success', 'data' => $result]);
        } catch (InvalidArgumentException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 404);
        } catch (RuntimeException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 409);
        }
    }
}