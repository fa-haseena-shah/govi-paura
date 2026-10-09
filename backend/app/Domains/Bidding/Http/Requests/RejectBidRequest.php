<?php

namespace App\Domains\Bidding\Http\Requests;

class RejectBidRequest extends BiddingRequest
{
    protected function allowedRoles(): array
    {
        return ['farmer'];
    }

    public function rules(): array
    {
        return [
            'reason' => ['nullable', 'string', 'max:255'],
        ];
    }
}
