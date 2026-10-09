<?php

namespace App\Domains\Bidding\Events;

use App\Domains\Bidding\Models\Bid;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired after a farmer rejects a bid. Available for a buyer notification; nothing is required to listen.
 */
class BidRejected
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly Bid $bid) {}
}
