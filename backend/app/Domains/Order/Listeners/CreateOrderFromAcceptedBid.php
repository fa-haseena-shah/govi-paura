<?php

namespace App\Domains\Order\Listeners;

use App\Domains\Bidding\Events\BidAccepted;
use App\Domains\Order\DTOs\CreateBidOrderData;
use App\Domains\Order\Services\OrderServiceInterface;
use RuntimeException;

// run parallely inside the Bidding domain's accept transaction.
// it creates an order (source = accepted_bid), reduces the listing stock.

class CreateOrderFromAcceptedBid
{
    public function __construct(private readonly OrderServiceInterface $orderService) {}

    public function handle(BidAccepted $event): void
    {
        $bid = $event->bid;
        $quantity = (float) $bid->bid_qty;

        // orders and listing stock are whole kilograms
        if ($quantity < 1 || floor($quantity) !== $quantity) {
            throw new RuntimeException('Only bids for a whole number of kilograms can be turned into an order.');
        }

        $this->orderService->createFromAcceptedBid(new CreateBidOrderData(
            buyerId: (int) $bid->buyer_id,
            bidId: (int) $bid->id,
            listingId: (int) $bid->listing_id,
            quantity: (int) $quantity,
            unitPrice: (float) $bid->bid_amount,   // price per kg
        ));
    }
}
