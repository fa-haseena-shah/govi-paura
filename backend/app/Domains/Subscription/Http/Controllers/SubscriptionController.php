<?php

namespace App\Domains\Subscription\Http\Controllers;

use App\Domains\Payment\Services\PaymentServiceInterface;
use App\Domains\Subscription\Http\Requests\PaySubscriptionRequest;
use App\Domains\Subscription\Http\Requests\RenewSubscriptionRequest;
use App\Domains\Subscription\Http\Requests\SubscribeRequest;
use App\Domains\Subscription\Services\SubscriptionServiceInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use RuntimeException;

class SubscriptionController extends Controller
{
    public function __construct(
        private readonly SubscriptionServiceInterface $subscriptions,
        private readonly PaymentServiceInterface $payments,
    ) {}

    public function plans(Request $request): JsonResponse
    {
        return $this->riderOnly($request, fn () => $this->subscriptions->plans());
    }

    public function subscribe(SubscribeRequest $request): JsonResponse
    {
        return $this->riderOnly($request, fn () => $this->subscriptions->subscribe($request->user(), $request->validated('plan')), 201);
    }

    public function renew(RenewSubscriptionRequest $request): JsonResponse
    {
        return $this->riderOnly($request, fn () => $this->subscriptions->renew($request->user(), $request->validated('plan')), 201);
    }

    public function status(Request $request): JsonResponse
    {
        return $this->riderOnly($request, fn () => $this->subscriptions->status($request->user()));
    }

    public function index(Request $request): JsonResponse
    {
        return $this->riderOnly($request, fn () => $this->subscriptions->history($request->user()));
    }

    public function pay(PaySubscriptionRequest $request, int $subscription): JsonResponse
    {
        return $this->riderOnly($request, fn () => $this->payments->payForSubscription($subscription, $request->user()->id, $request->validated('method')), 201);
    }

    public function cancel(Request $request, int $subscription): JsonResponse
    {
        return $this->riderOnly($request, fn () => $this->subscriptions->cancel($request->user(), $subscription));
    }

    private function riderOnly(Request $request, callable $action, int $successCode = 200): JsonResponse
    {
        if (!$request->user()->isRider()) {
            return response()->json(['status' => 'error', 'message' => 'Only riders have subscriptions.'], 403);
        }

        try {
            return response()->json(['status' => 'success', 'data' => $action()], $successCode);
        } catch (InvalidArgumentException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 404);
        } catch (RuntimeException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 409);
        }
    }
}
