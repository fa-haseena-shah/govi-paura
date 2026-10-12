<?php

namespace App\Domains\Subscription\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaySubscriptionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        // subscriptions are card-only
        return ['method' => ['required', 'in:card']];
    }
}
