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
    Route::get('/sms-logs', [SmsLogController::class, 'index']);
});

Route::get('/listings', [ListingController::class, 'index']);
Route::get('/listings/{listing}', [ListingController::class, 'show']);