<?php

namespace App\Domains\Listing\Http\Requests;

use App\Enums\SellingMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class CreateListingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'crop_type' => ['required', 'string', 'max:100'],
            'qty' => ['required', 'integer', 'min:1'],
            'harvest_date' => ['required', 'date'],
            'location' => ['required', 'string', 'max:255'],
            'selling_method' => ['required', new Enum(SellingMethod::class)],
            'photo' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:5120'],
            'price_per_unit' => ['required_if:selling_method,' . SellingMethod::FixedPrice->value, 'nullable', 'numeric', 'min:0.01'],
            'starting_price' => ['required_if:selling_method,' . SellingMethod::Bidding->value, 'nullable', 'numeric', 'min:0.01'],
            'closing_time' => ['required_if:selling_method,' . SellingMethod::Bidding->value, 'nullable', 'date', 'after:now'],
        ];
    }
}