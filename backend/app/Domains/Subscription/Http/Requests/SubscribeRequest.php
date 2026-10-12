<?php

namespace App\Domains\Subscription\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubscribeRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return ['plan' => ['required', 'string', Rule::in(array_keys(config('subscriptions.plans')))]];
    }
}
