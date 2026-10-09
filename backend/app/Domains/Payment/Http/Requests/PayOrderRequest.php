<?php

namespace App\Domains\Payment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\In;

class PayOrderRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'method' => ['required', 'string', new In(['card', 'cash_on_delivery'])],
        ];
    }
}