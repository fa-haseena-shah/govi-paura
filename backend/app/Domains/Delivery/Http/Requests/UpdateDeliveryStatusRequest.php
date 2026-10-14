<?php

namespace App\Domains\Delivery\Http\Requests;

use App\Enums\DeliveryStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDeliveryStatusRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            // "assigned" is set by claiming the delivery, so riders only move it forward from there
            'status' => ['required', 'string', Rule::in([
                DeliveryStatus::PickedUp->value,
                DeliveryStatus::InTransit->value,
                DeliveryStatus::Delivered->value,
            ])],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }
}
