<?php

namespace App\Domains\Bidding\Http\Requests;

use App\Enums\BidStatus;
use Illuminate\Validation\Rules\Enum;

abstract class BidFilterRequest extends BiddingRequest
{
    public function rules(): array
    {
        return [
            'status' => ['nullable', new Enum(BidStatus::class)],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
