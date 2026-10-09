<?php

namespace App\Domains\Notification\Listeners;

use App\Domains\Bidding\Events\BidPlaced;
use App\Domains\Notification\Services\SmsAlertServiceInterface;
use Throwable;

// texts the farmer when a buyer places a bid on their listing
class SendNewBidAlert
{
    public function __construct(private readonly SmsAlertServiceInterface $smsAlertService) {}

    public function handle(BidPlaced $event): void
    {
        try {
            $bid = $event->bid->loadMissing('listing.farmer', 'buyer.buyerProfile');

            $listing = $bid->listing;
            $farmer = $listing?->farmer;
            $buyer = $bid->buyer;

            if ($farmer === null || $buyer === null) {
                return;
            }

            $this->smsAlertService->sendNewBidAlert(
                $farmer,
                $buyer->buyerProfile?->business_name ?? $buyer->full_name,
                (float) $bid->bid_amount,
                $listing->crop_type,
            );
        } catch (Throwable $e) {
            report($e);
        }
    }
}
