<?php

namespace App\Domains\Listing\Events;

use App\Domains\Listing\Models\Listing;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * A listing's stock fell to the low-stock threshold, or to zero.
 * Fires after the surrounding database transaction commits, so a rolled-back change never notifies anyone.
 */
class StockLow implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly Listing $listing) {}
}
