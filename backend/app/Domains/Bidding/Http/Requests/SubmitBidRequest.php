<?php

namespace App\Domains\Bidding\Http\Requests;

class SubmitBidRequest extends BiddingRequest
{
    protected function allowedRoles(): array
    {
        return ['buyer'];
    }

    public function rules(): array
    {
        return [
            'listing_id' => ['required', 'integer', 'min:1'],
            'amount' => ['required', 'numeric', 'decimal:0,2', 'min:0.01', 'max:99999999.99'],
            'quantity' => ['required', 'integer', 'min:1', 'max:99999999.99'],
        ];
    }
}
