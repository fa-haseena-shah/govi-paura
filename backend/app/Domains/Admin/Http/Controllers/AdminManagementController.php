<?php

namespace App\Domains\Admin\Http\Controllers;

use App\Domains\Bidding\Models\Bid;
use App\Domains\Listing\Models\Listing;
use App\Domains\Order\Events\OrderStatusChanged;
use App\Domains\Order\Models\Order;
use App\Domains\Payment\Models\Payment;
use App\Domains\Subscription\Models\Subscription;
use App\Enums\OrderStatus;
use App\Enums\PaymentTransactionStatus;
use App\Enums\SubscriptionStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminManagementController extends Controller
{
    private const ALLOWED_ORDER_NEXT = [
        'pending' => ['confirmed'],
        'confirmed' => ['processing'],
        'processing' => ['delivered'],
        'delivered' => ['completed'],
        'completed' => [],
    ];

    public function users(Request $request): JsonResponse
    {
        $this->ensureAdmin($request);
        $query = User::query()->select(['id', 'full_name', 'email', 'phone', 'role', 'status', 'created_at']);
        if ($request->filled('search')) {
            $search = $request->string('search')->trim()->toString();
            $query->where(fn ($builder) => $builder
                ->where('full_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%"));
        }
        if ($request->filled('role')) $query->where('role', $request->query('role'));
        if ($request->filled('status')) $query->where('status', $request->query('status'));

        return response()->json(['status' => 'success', ...$this->page($query->latest(), $request)]);
    }

    public function updateUserStatus(Request $request, User $user): JsonResponse
    {
        $this->ensureAdmin($request);
        $validated = $request->validate(['status' => ['required', Rule::in(['active', 'suspended'])]]);
        abort_if($user->isAdmin() || $user->is($request->user()), 422, 'Administrator accounts cannot be suspended here.');
        $user->update(['status' => $validated['status']]);

        return response()->json(['status' => 'success', 'data' => ['id' => $user->id, 'status' => $user->status->value]]);
    }

    public function listings(Request $request): JsonResponse
    {
        $this->ensureAdmin($request);
        $query = Listing::query()->with('farmer:id,full_name')->latest();
        if ($request->filled('status')) $query->where('status', $request->query('status'));
        if ($request->filled('search')) {
            $search = $request->string('search')->trim()->toString();
            $query->where('crop_type', 'like', "%{$search}%");
        }

        return response()->json(['status' => 'success', ...$this->page($query, $request)]);
    }

    public function removeListing(Request $request, Listing $listing): JsonResponse
    {
        $this->ensureAdmin($request);
        abort_if($listing->status?->value !== 'active', 409, 'Only active listings can be removed.');
        $listing->update(['status' => 'closed']);

        return response()->json(['status' => 'success', 'data' => ['id' => $listing->id, 'status' => 'closed']]);
    }

    public function assistedListing(Request $request): JsonResponse
    {
        $this->ensureAdmin($request);
        $validated = $request->validate([
            'farmer_id' => ['required', 'integer', 'exists:users,id'],
            'crop_type' => ['required', 'string', 'max:100'],
            'qty' => ['required', 'integer', 'min:1'],
            'price_per_unit' => ['nullable', 'numeric', 'min:0'],
            'harvest_date' => ['required', 'date', 'after_or_equal:today'],
            'location' => ['required', 'string', 'max:255'],
            'selling_method' => ['required', Rule::in(['fixed_price', 'bidding'])],
            'starting_price' => ['nullable', 'numeric', 'min:0'],
            'closing_time' => ['nullable', 'date', 'after:now'],
        ]);
        $farmer = User::query()->findOrFail($validated['farmer_id']);
        abort_unless($farmer->isFarmer() && $farmer->isActive(), 422, 'Choose an active farmer account.');
        abort_if($validated['selling_method'] === 'fixed_price' && empty($validated['price_per_unit']), 422, 'A fixed-price listing needs a price per unit.');
        abort_if($validated['selling_method'] === 'bidding' && empty($validated['starting_price']), 422, 'A bidding listing needs a starting price.');

        $listing = Listing::query()->create([...$validated, 'status' => 'active']);

        return response()->json(['status' => 'success', 'data' => $listing->load('farmer:id,full_name')], 201);
    }

    public function activity(Request $request): JsonResponse
    {
        $this->ensureAdmin($request);
        $validated = $request->validate([
            'type' => ['required', Rule::in(['bids', 'orders', 'subscriptions'])],
            'search' => ['nullable', 'string', 'max:255'],
            'subscription_status' => ['nullable', Rule::in(array_map(fn ($status) => $status->value, SubscriptionStatus::cases()))],
            'transaction_status' => ['nullable', Rule::in(array_map(fn ($status) => $status->value, PaymentTransactionStatus::cases()))],
        ]);
        $type = $validated['type'];

        if ($type === 'subscriptions') {
            $baseQuery = Subscription::query()
                ->with(['rider.user:id,full_name,email,phone', 'payments:id,payable_id,payable_type,reference,amount,currency,status,method,created_at'])
                ->when($validated['subscription_status'] ?? null, fn ($query, $status) => $query->where('status', $status));

            if (!empty($validated['search'])) {
                $search = trim($validated['search']);
                $baseQuery->where(fn ($query) => $query
                    ->whereHas('rider.user', fn ($user) => $user
                        ->where('full_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%"))
                    ->orWhereHas('payments', fn ($payment) => $payment
                        ->where('reference', 'like', "%{$search}%")));
            }

            $query = clone $baseQuery;
            $summaryQuery = clone $baseQuery;
            if (!empty($validated['transaction_status'])) {
                $transactionStatus = $validated['transaction_status'];
                $query->whereHas('payments', fn ($payment) => $payment->where('status', $transactionStatus))
                    ->with(['payments' => fn ($payment) => $payment->where('status', $transactionStatus)]);
                $summaryQuery->whereHas('payments', fn ($payment) => $payment->where('status', $transactionStatus));
            }

            $subscriptionIds = (clone $summaryQuery)->reorder()->select('subscriptions.id');
            $paymentQuery = Payment::query()
                ->where('payable_type', (new Subscription())->getMorphClass())
                ->whereIn('payable_id', $subscriptionIds);
            if (!empty($validated['transaction_status'])) {
                $paymentQuery->where('status', $validated['transaction_status']);
            }
            $successfulPayments = (clone $paymentQuery)->where('status', PaymentTransactionStatus::Success->value);
            $summary = [
                'subscriptions' => (clone $summaryQuery)->count(),
                'transactions' => (clone $paymentQuery)->count(),
                'successful_transactions' => (clone $paymentQuery)->where('status', PaymentTransactionStatus::Success->value)->count(),
                'pending_transactions' => (clone $paymentQuery)->where('status', PaymentTransactionStatus::Pending->value)->count(),
                'attention_transactions' => (clone $paymentQuery)->whereIn('status', [
                    PaymentTransactionStatus::Failed->value,
                    PaymentTransactionStatus::Cancelled->value,
                    PaymentTransactionStatus::ChargedBack->value,
                ])->count(),
                'successful_volume' => (float) $successfulPayments->sum('amount'),
            ];

            return response()->json(['status' => 'success', ...$this->page($query->latest(), $request), 'summary' => $summary]);
        }

        $query = match ($type) {
            'bids' => Bid::query()->with(['buyer:id,full_name', 'listing:id,farmer_id,crop_type', 'listing.farmer:id,full_name'])->latest(),
            'orders' => Order::query()->with(['buyer:id,full_name', 'farmer:id,full_name', 'items:id,order_id,listing_id,qty,unit_price,sub_total'])->latest(),
        };

        return response()->json(['status' => 'success', ...$this->page($query, $request)]);
    }

    public function updateOrderStatus(Request $request, Order $order): JsonResponse
    {
        $this->ensureAdmin($request);
        $validated = $request->validate(['status' => ['required', Rule::in(array_map(fn ($status) => $status->value, OrderStatus::cases()))]]);
        $previous = $order->status;
        abort_unless(in_array($validated['status'], self::ALLOWED_ORDER_NEXT[$previous->value], true), 409, "Cannot transition from {$previous->value} to {$validated['status']}.");
        $order->update(['status' => $validated['status']]);
        OrderStatusChanged::dispatch($order->fresh(), $previous);

        return response()->json(['status' => 'success', 'data' => ['id' => $order->id, 'status' => $order->status->value]]);
    }

    public function reports(Request $request): JsonResponse
    {
        $this->ensureAdmin($request);
        $validated = $request->validate([
            'type' => ['required', Rule::in(['users', 'listings', 'bids', 'orders'])],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);
        $model = match ($validated['type']) {
            'users' => User::query(),
            'listings' => Listing::query(),
            'bids' => Bid::query(),
            default => Order::query(),
        };
        if (!empty($validated['from'])) $model->whereDate('created_at', '>=', $validated['from']);
        if (!empty($validated['to'])) $model->whereDate('created_at', '<=', $validated['to']);

        $series = (clone $model)->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')->orderBy('date')->get();
        $summary = ['total' => (clone $model)->count()];
        if ($validated['type'] === 'users') {
            $summary['by_role'] = (clone $model)->selectRaw('role, COUNT(*) as count')->groupBy('role')->pluck('count', 'role');
            $summary['by_status'] = (clone $model)->selectRaw('status, COUNT(*) as count')->groupBy('status')->pluck('count', 'status');
        } elseif ($validated['type'] === 'listings') {
            $summary['by_status'] = (clone $model)->selectRaw('status, COUNT(*) as count')->groupBy('status')->pluck('count', 'status');
            $summary['by_crop'] = (clone $model)->selectRaw('crop_type, COUNT(*) as count')->groupBy('crop_type')->orderByDesc('count')->limit(10)->pluck('count', 'crop_type');
        } elseif ($validated['type'] === 'bids') {
            $summary['by_status'] = (clone $model)->selectRaw('bid_status as status, COUNT(*) as count')->groupBy('bid_status')->pluck('count', 'status');
        } else {
            $summary['by_status'] = (clone $model)->selectRaw('status, COUNT(*) as count')->groupBy('status')->pluck('count', 'status');
            $summary['purchasing_volume'] = (float) (clone $model)->sum('total_amount');
        }

        return response()->json(['status' => 'success', 'data' => ['type' => $validated['type'], 'summary' => $summary, 'series' => $series]]);
    }

    public function supportContact(Request $request): JsonResponse
    {
        $this->ensureAdmin($request);
        return $this->contactResponse();
    }

    public function publicSupportContact(): JsonResponse
    {
        return $this->contactResponse();
    }

    public function updateSupportContact(Request $request): JsonResponse
    {
        $this->ensureAdmin($request);
        $contact = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
        ]);
        DB::table('admin_settings')->updateOrInsert(
            ['key' => 'support_contact'],
            ['value' => json_encode($contact), 'updated_at' => now(), 'created_at' => now()],
        );

        return response()->json(['status' => 'success', 'data' => $contact]);
    }

    private function page($query, Request $request): array
    {
        $perPage = min(max((int) $request->query('per_page', 20), 1), 100);
        $page = $query->paginate($perPage);

        return [
            'data' => $page->items(),
            'total' => $page->total(),
            'page' => $page->currentPage(),
            'perPage' => $page->perPage(),
        ];
    }

    private function ensureAdmin(Request $request): void
    {
        abort_unless($request->user()?->isAdmin(), 403, 'Only administrators can access this resource.');
    }

    private function contactResponse(): JsonResponse
    {
        $contact = DB::table('admin_settings')->where('key', 'support_contact')->value('value');

        return response()->json(['status' => 'success', 'data' => $contact
            ? json_decode($contact, true)
            : ['email' => 'support@govipaura.lk', 'phone' => '+94112345678']]);
    }
}