<?php

namespace App\Domains\Admin\Http\Controllers;

use App\Domains\Listing\Models\Listing;
use App\Domains\Order\Models\Order;
use App\Domains\Payment\Models\Payment;
use App\Enums\ListingStatus;
use App\Enums\PaymentTransactionStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        if (! $request->user()->isAdmin()) {
            return response()->json(['status' => 'error', 'message' => 'Only admins can view dashboard metrics.'], 403);
        }

        $monthStart = now()->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();

        $paidOrderVolume = Payment::query()
            ->where('payable_type', (new Order())->getMorphClass())
            ->where('status', PaymentTransactionStatus::Success->value)
            ->sum('amount');

        return response()->json([
            'status' => 'success',
            'data' => [
                'total_users' => User::query()->count(),
                'active_listings' => Listing::query()->where('status', ListingStatus::Active->value)->count(),
                'orders_this_month' => Order::query()->whereBetween('created_at', [$monthStart, $monthEnd])->count(),
                'paid_order_volume' => (float) $paidOrderVolume,
            ],
        ]);
    }
}