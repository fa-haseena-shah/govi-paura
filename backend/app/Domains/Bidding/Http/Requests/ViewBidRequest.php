<?php

namespace App\Domains\Bidding\Http\Requests;

/** Read-only endpoints usable by both sides of a bid (buyer and farmer). */
class ViewBidRequest extends BiddingRequest
{
    protected function allowedRoles(): array
    {
        return ['buyer', 'farmer'];
    }
}
