<?php

namespace App\Domains\Bidding\Http\Requests;

class AcceptBidRequest extends BiddingRequest
{
    protected function allowedRoles(): array
    {
        return ['farmer'];
    }
}
