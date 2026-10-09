<?php

namespace App\Domains\Listing\Http\Controllers;

use App\Domains\Listing\DTOs\CreateListingRequestData;
use App\Domains\Listing\DTOs\FilterListingData;
use App\Domains\Listing\DTOs\UpdateListingRequestData;
use App\Domains\Listing\Http\Requests\CreateListingRequest;
use App\Domains\Listing\Http\Requests\UpdateListingRequest;
use App\Domains\Listing\Services\ListingServiceInterface;
use App\Enums\UserType;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use RuntimeException;

class ListingController extends Controller
{
    public function __construct(
        private readonly ListingServiceInterface $listingService,
    ) {}

    public function store(CreateListingRequest $request): JsonResponse
    {
        if (!$request->user()->isFarmer()) {
            return response()->json(['status' => 'error', 'message' => 'Only farmers can create listings.'], 403);
        }

        try {
            $photoUrl = $request->hasFile('photo')
                ? Storage::disk('public')->url($request->file('photo')->store('listings', 'public'))
                : null;

            $data = CreateListingRequestData::fromArray($request->validated(), $request->user()->id, $photoUrl);
            $result = $this->listingService->create($data);

            return response()->json(['status' => 'success', 'data' => $result], 201);
        } catch (InvalidArgumentException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }

    public function update(UpdateListingRequest $request, int $listing): JsonResponse
    {
        try {
            $photoUrl = $request->hasFile('photo')
                ? Storage::disk('public')->url($request->file('photo')->store('listings', 'public'))
                : null;

            $data = UpdateListingRequestData::fromArray($request->validated(), $listing, $request->user()->id, $photoUrl);
            $result = $this->listingService->update($data);

            return response()->json(['status' => 'success', 'data' => $result]);
        } catch (InvalidArgumentException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 404);
        } catch (RuntimeException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 403);
        }
    }

    public function close(Request $request, int $listing): JsonResponse
    {
        try {
            $result = $this->listingService->close($listing, $request->user()->id);
            return response()->json(['status' => 'success', 'data' => $result]);
        } catch (InvalidArgumentException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 404);
        } catch (RuntimeException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 409);
        }
    }

    public function show(int $listing): JsonResponse
    {
        try {
            $result = $this->listingService->viewDetails($listing);
            return response()->json(['status' => 'success', 'data' => $result]);
        } catch (InvalidArgumentException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 404);
        }
    }

    public function mine(Request $request): JsonResponse
    {
        if (!$request->user()->isFarmer()) {
            return response()->json(['status' => 'error', 'message' => 'Only farmers have listings of their own.'], 403);
        }

        return response()->json(['status' => 'success', 'data' => $this->listingService->forFarmer($request->user()->id)]);
    }

    public function index(Request $request): JsonResponse
    {
        $filter = FilterListingData::fromArray($request->query());
        $result = $this->listingService->browse($filter);

        return response()->json(['status' => 'success', ...$result]);
    }
}