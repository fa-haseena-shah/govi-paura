<?php

namespace App\Domains\Delivery\Http\Controllers;

use App\Domains\Delivery\Exceptions\DeliveryAccessException;
use App\Domains\Delivery\Http\Requests\UpdateLocationRequest;
use App\Domains\Delivery\Services\DeliveryServiceInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * A farmer's saved location is where riders pick up; a buyer's is where they drop off.
 */
class DeliveryLocationController extends Controller
{
    public function __construct(private readonly DeliveryServiceInterface $deliveries) {}

    public function show(Request $request): JsonResponse
    {
        try {
            return response()->json(['status' => 'success', 'data' => $this->deliveries->getLocation($request->user())]);
        } catch (DeliveryAccessException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 403);
        }
    }

    public function update(UpdateLocationRequest $request): JsonResponse
    {
        try {
            return response()->json(['status' => 'success', 'data' => $this->deliveries->saveLocation($request->user(), $request->validated())]);
        } catch (DeliveryAccessException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 403);
        }
    }
}
