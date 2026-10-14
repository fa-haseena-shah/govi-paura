<?php

namespace App\Domains\Listing\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateListingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $latestClosingTime = now()->addMonthsNoOverflow(2)->toDateTimeString();

        return [
            'crop_type' => ['sometimes', 'string', 'max:100'],
            'qty' => ['sometimes', 'integer', 'min:0'],
            'harvest_date' => ['sometimes', 'date'],
            'location' => ['sometimes', 'string', 'max:255'],
            'photo' => ['sometimes', 'nullable', 'file', 'mimes:jpg,jpeg,png', 'max:5120'],
            'price_per_unit' => ['sometimes', 'numeric', 'min:0.01'],
            'starting_price' => ['sometimes', 'numeric', 'min:0.01'],
            'closing_time' => ['sometimes', 'date', 'after:now', 'before_or_equal:' . $latestClosingTime],
        ];
    }

    public function messages(): array
    {
        return [
            'closing_time.before_or_equal' => 'The bidding close time must be within the next two months.',
        ];
    }
}