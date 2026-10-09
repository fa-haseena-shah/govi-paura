<?php

namespace App\Domains\Bidding\Http\Requests;

class ListListingBidsRequest extends BidFilterRequest
{
    protected function allowedRoles(): array
    {
        return ['farmer'];
    }
}
