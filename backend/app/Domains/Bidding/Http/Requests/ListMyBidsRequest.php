<?php

namespace App\Domains\Bidding\Http\Requests;

class ListMyBidsRequest extends BidFilterRequest
{
    protected function allowedRoles(): array
    {
        return ['buyer'];
    }
}
