<?php

namespace App\Domains\Subscription\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RenewSubscriptionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        // optional: renewing without a plan repeats the rider's last plan
        return ['plan' => ['sometimes', 'string', Rule::in(array_keys(config('subscriptions.plans')))]];
    }
}
