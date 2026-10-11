<?php

namespace App\Domains\Listing\Events;

use App\Domains\Listing\Models\Listing;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * A bidding listing's closing time passed and the listing was closed.
 * Fires after the surrounding database transaction commits, so a rolled-back change never notifies anyone.
 */
class BiddingClosed implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly Listing $listing) {}
}
