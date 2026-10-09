<?php

namespace App\Domains\Order\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateOrderRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.listing_id' => ['required', 'integer', 'distinct', 'exists:listings,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }
}
