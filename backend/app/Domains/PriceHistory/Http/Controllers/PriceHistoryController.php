<?php

namespace App\Domains\PriceHistory\Http\Controllers;

use App\Domains\PriceHistory\DTOs\PriceHistoryFilterData;
use App\Domains\PriceHistory\Http\Requests\PriceHistoryRequest;
use App\Domains\PriceHistory\Services\PriceHistoryServiceInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PriceHistoryController extends Controller
{
    public function __construct(private readonly PriceHistoryServiceInterface $priceHistory) {}

    // GET /price-history?crop_type=&region=&from=&to=&page=&per_page=
    public function index(PriceHistoryRequest $request): JsonResponse
    {
        if ($denied = $this->denyUnlessAllowed($request)) {
            return $denied;
        }

        $result = $this->priceHistory->history(PriceHistoryFilterData::fromArray($request->validated()));

        return response()->json(['status' => 'success', ...$result]);
    }

    // GET /price-history/trends?crop_type=&region=&from=&to=&interval=day|week|month
    public function trends(PriceHistoryRequest $request): JsonResponse
    {
        if ($denied = $this->denyUnlessAllowed($request)) {
            return $denied;
        }

        $result = $this->priceHistory->trends(PriceHistoryFilterData::fromArray($request->validated()));

        return response()->json(['status' => 'success', 'data' => $result]);
    }

    // GET /price-history/filters  (crops and regions that have price data, for the dropdowns)
    public function filters(Request $request): JsonResponse
    {
        if ($denied = $this->denyUnlessAllowed($request)) {
            return $denied;
        }

        return response()->json(['status' => 'success', 'data' => $this->priceHistory->filterOptions()]);
    }

    private function denyUnlessAllowed(Request $request): ?JsonResponse
    {
        $user = $request->user();

        if ($user->isFarmer() || $user->isBuyer() || $user->isAdmin()) {
            return null;
        }

        return response()->json(['status' => 'error', 'message' => 'Only farmers and buyers can view price history.'], 403);
    }
}
