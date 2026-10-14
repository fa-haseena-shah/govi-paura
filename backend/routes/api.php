<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Domains\Auth\Http\Controllers\AuthController;
use App\Domains\Listing\Http\Controllers\ListingController;
use App\Domains\Order\Http\Controllers\OrderController;
use App\Domains\Payment\Http\Controllers\PaymentController;
use App\Domains\Notification\Http\Controllers\SmsLogController;
use App\Domains\Bidding\Http\Controllers\BidController;
use App\Domains\Verification\Http\Controllers\VerificationController;
use App\Domains\Admin\Http\Controllers\DashboardController;
use App\Domains\Admin\Http\Controllers\AdminManagementController;
use App\Domains\Notification\Http\Controllers\NotificationController;
use App\Domains\Subscription\Http\Controllers\SubscriptionController;
use App\Domains\Rating\Http\Controllers\RatingController;
use App\Domains\PriceHistory\Http\Controllers\PriceHistoryController;
use App\Domains\Delivery\Http\Controllers\DeliveryController;
use App\Domains\Delivery\Http\Controllers\DeliveryLocationController;
use App\Http\Middleware\EnsureAccountActive;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::get('/verification', [VerificationController::class, 'show']);
    Route::post('/verification/documents', [VerificationController::class, 'upload']);
    Route::get('/verification/documents/{document}/file', [VerificationController::class, 'file']);
});

Route::middleware(['auth:sanctum', EnsureAccountActive::class])->group(function () {
    Route::get('/admin/dashboard', DashboardController::class);
    Route::get('/admin/users', [AdminManagementController::class, 'users']);
    Route::patch('/admin/users/{user}/status', [AdminManagementController::class, 'updateUserStatus']);
    Route::get('/admin/listings', [AdminManagementController::class, 'listings']);
    Route::post('/admin/listings/{listing}/remove', [AdminManagementController::class, 'removeListing']);
    Route::post('/admin/assisted-listings', [AdminManagementController::class, 'assistedListing']);
    Route::get('/admin/activity', [AdminManagementController::class, 'activity']);
    Route::patch('/admin/orders/{order}/status', [AdminManagementController::class, 'updateOrderStatus']);
    Route::get('/admin/reports', [AdminManagementController::class, 'reports']);
    Route::get('/admin/support-contact', [AdminManagementController::class, 'supportContact']);
    Route::put('/admin/support-contact', [AdminManagementController::class, 'updateSupportContact']);
    Route::get('/admin/verifications', [VerificationController::class, 'index']);
    Route::get('/admin/verifications/{user}', [VerificationController::class, 'detail']);
    Route::post('/admin/verifications/{user}/approve', [VerificationController::class, 'approve']);
    Route::post('/admin/verifications/{user}/reject', [VerificationController::class, 'reject']);
    Route::post('/listings', [ListingController::class, 'store']);
    Route::get('/listings/mine', [ListingController::class, 'mine']);
    Route::put('/listings/{listing}', [ListingController::class, 'update']);
    Route::post('/listings/{listing}/close', [ListingController::class, 'close']);
    Route::post('/checkout/preview', [OrderController::class, 'preview']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/sales-history', [OrderController::class, 'salesHistory']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus']);
    Route::post('/orders/{order}/pay', [PaymentController::class, 'payForOrder']);
    Route::post('/orders/{order}/confirm-cash-collected', [PaymentController::class, 'confirmCashCollected']);
    Route::post('/bids', [BidController::class, 'store']); // buyer: submit bid
    Route::get('/bids/my', [BidController::class, 'myBids']); // buyer: my bids + status
    Route::get('/bids/{bid}', [BidController::class, 'show'])->whereNumber('bid');
    Route::post('/bids/{bid}/accept', [BidController::class, 'accept'])->whereNumber('bid'); 
    Route::post('/bids/{bid}/reject', [BidController::class, 'reject'])->whereNumber('bid');
    Route::get('/listings/{listing}/bids', [BidController::class, 'listingBids'])->whereNumber('listing');
    Route::get('/listings/{listing}/bid-summary', [BidController::class, 'summary'])->whereNumber('listing'); // buyer/farmer
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead']);
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead']);
    Route::get('/sms-logs', [SmsLogController::class, 'index']);
    Route::get('/subscriptions/plans', [SubscriptionController::class, 'plans']);
    Route::get('/subscriptions/status', [SubscriptionController::class, 'status']);
    Route::get('/subscriptions', [SubscriptionController::class, 'index']);
    Route::post('/subscriptions', [SubscriptionController::class, 'subscribe']);
    Route::post('/subscriptions/renew', [SubscriptionController::class, 'renew']);
    Route::post('/subscriptions/{subscription}/pay', [SubscriptionController::class, 'pay']);
    Route::post('/subscriptions/{subscription}/cancel', [SubscriptionController::class, 'cancel']);
    Route::post('/orders/{order}/ratings', [RatingController::class, 'store']);
    Route::get('/orders/{order}/ratings', [RatingController::class, 'forOrder']);
    Route::get('/ratings/received', [RatingController::class, 'received']);
    Route::get('/ratings/given', [RatingController::class, 'given']);
    Route::get('/users/{user}/ratings', [RatingController::class, 'forUser']);
    Route::get('/price-history', [PriceHistoryController::class, 'index']);
    Route::get('/price-history/trends', [PriceHistoryController::class, 'trends']);
    Route::get('/price-history/filters', [PriceHistoryController::class, 'filters']);
    Route::get('/me/location', [DeliveryLocationController::class, 'show']);
    Route::put('/me/location', [DeliveryLocationController::class, 'update']);
    Route::get('/deliveries/opportunities', [DeliveryController::class, 'opportunities']);
    Route::get('/deliveries/assigned', [DeliveryController::class, 'assigned']);
    Route::get('/deliveries/history', [DeliveryController::class, 'history']);
    Route::get('/deliveries/tracking', [DeliveryController::class, 'tracking']);
    Route::get('/deliveries/{delivery}', [DeliveryController::class, 'show'])->whereNumber('delivery');
    Route::get('/deliveries/{delivery}/map', [DeliveryController::class, 'map'])->whereNumber('delivery');
    Route::post('/deliveries/{delivery}/claim', [DeliveryController::class, 'claim'])->whereNumber('delivery');
    Route::post('/deliveries/{delivery}/release', [DeliveryController::class, 'release'])->whereNumber('delivery');
    Route::patch('/deliveries/{delivery}/status', [DeliveryController::class, 'updateStatus'])->whereNumber('delivery');
    Route::get('/orders/{order}/delivery', [DeliveryController::class, 'forOrder'])->whereNumber('order');
    Route::get('/admin/deliveries', [DeliveryController::class, 'adminIndex']);
});

Route::get('/listings', [ListingController::class, 'index']);
Route::get('/listings/{listing}', [ListingController::class, 'show']);
Route::get('/support-contact', [AdminManagementController::class, 'publicSupportContact']);