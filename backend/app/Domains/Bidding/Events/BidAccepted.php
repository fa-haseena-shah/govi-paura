<?php

namespace App\Domains\Bidding\Events;

use App\Domains\Bidding\Models\Bid;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired INSIDE the accept transaction. The Order domain listens (synchronously) to create the order and reduce listing stock; if the listener throws, the acceptance rolls back.
 */
class BidAccepted
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly Bid $bid) {}
}
