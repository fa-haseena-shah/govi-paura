<?php

namespace App\Domains\Bidding\Events;

use App\Domains\Bidding\Models\Bid;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired after a buyer's bid is saved. The SMS Alerts domain listens to this to text the farmer (new bid alert).
 */
class BidPlaced
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly Bid $bid) {}
}
