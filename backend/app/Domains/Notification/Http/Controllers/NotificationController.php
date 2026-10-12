<?php

namespace App\Domains\Notification\Http\Controllers;

use App\Domains\Notification\Services\NotificationServiceInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class NotificationController extends Controller
{
    public function __construct(
        private readonly NotificationServiceInterface $notificationService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filter = $request->query('status') === 'unread' ? 'unread' : 'all';
        $page = max(1, (int) $request->query('page', 1));
        $perPage = min(50, max(1, (int) $request->query('per_page', 20)));

        $result = $this->notificationService->listFor($request->user(), $filter, $page, $perPage);

        return response()->json(['status' => 'success', ...$result]);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => ['count' => $this->notificationService->unreadCount($request->user())],
        ]);
    }

    public function markRead(Request $request, int $notification): JsonResponse
    {
        try {
            return response()->json([
                'status' => 'success',
                'data' => $this->notificationService->markRead($request->user(), $notification),
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 404);
        }
    }

    public function markAllRead(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => ['marked' => $this->notificationService->markAllRead($request->user())],
        ]);
    }
}
