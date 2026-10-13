<?php

namespace App\Domains\Rating\Events;

use App\Domains\Rating\Models\Rating;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

// for the review notifications
class RatingSubmitted implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly Rating $rating) {}
}
