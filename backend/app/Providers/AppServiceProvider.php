<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Event;

use App\Domains\Auth\Services\AuthService;
use App\Domains\Auth\Services\AuthServiceInterface;
use App\Domains\Listing\Services\ListingService;
use App\Domains\Listing\Services\ListingServiceInterface;
use App\Domains\Order\Models\Order;
use App\Domains\Order\Services\OrderService;
use App\Domains\Order\Services\OrderServiceInterface;
use App\Domains\Payment\Services\PaymentService;
use App\Domains\Subscription\Models\Subscription;
use App\Domains\Payment\Services\PaymentServiceInterface;
use App\Domains\Notification\Services\LogSmsGateway;
use App\Domains\Notification\Services\SmsGoGateway;
use App\Domains\Notification\Services\SmsAlertService;
use App\Domains\Notification\Services\SmsAlertServiceInterface;
use App\Domains\Notification\Services\SmsGatewayInterface;
use App\Domains\Bidding\Services\BidService;
use App\Domains\Bidding\Services\BidServiceInterface;
use App\Domains\Bidding\Events\BidAccepted;
use App\Domains\Bidding\Events\BidPlaced;
use App\Domains\Bidding\Events\BidRejected;
use App\Domains\Order\Listeners\CreateOrderFromAcceptedBid;
use App\Domains\Notification\Listeners\SendNewBidAlert;
use App\Domains\Verification\Services\VerificationServiceInterface;
use App\Domains\Verification\Services\VerificationService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
        $this->app->bind(VerificationServiceInterface::class, VerificationService::class,);
        $this->app->bind(ListingServiceInterface::class, ListingService::class);
        $this->app->bind(OrderServiceInterface::class, OrderService::class);
        $this->app->bind(PaymentServiceInterface::class, PaymentService::class);
        $this->app->bind(SmsGatewayInterface::class, function () {
            return config('govipaura.sms_driver') === 'smsgo'
                ? app(SmsGoGateway::class)
                : app(LogSmsGateway::class);
        });
        $this->app->bind(SmsAlertServiceInterface::class, SmsAlertService::class);
        $this->app->bind(BidServiceInterface::class, BidService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        // short aliases stored in payments.payable_type
        Relation::morphMap([
            'order' => Order::class,
            'subscription' => Subscription::class,
        ]);

        Event::listen(BidAccepted::class, CreateOrderFromAcceptedBid::class);
        Event::listen(BidPlaced::class, SendNewBidAlert::class);
    }
}
