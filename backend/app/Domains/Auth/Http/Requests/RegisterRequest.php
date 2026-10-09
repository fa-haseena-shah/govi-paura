<?php

namespace App\Domains\Auth\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use App\Enums\BusinessType;
use App\Enums\Language;
use App\Enums\DeliveryCategory;
use App\Enums\UserType;
use App\Enums\VehicleType;
use Illuminate\Validation\Rules\Enum;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // shared fields
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required_unless:role,' . UserType::Farmer->value, 'nullable', 'string', 'email', 'max:255', 'unique:users,email'],            
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'preferred_lang' => ['required', new Enum(Language::class)],
            'role' => ['required', new Enum(UserType::class)],

            // farmers only
            'nic' => ['required_if:role,' . UserType::Farmer->value, 'nullable', 'string', 'max:20', 'unique:farmers,nic'],
            'address' => ['required_if:role,' . UserType::Farmer->value, 'nullable', 'string', 'max:500'],
            'region' => ['nullable', 'string', 'max:255'],

            // buyers only
            'business_name' => ['required_if:role,' . UserType::Buyer->value, 'nullable', 'string', 'max:255'],
            'business_type' => ['required_if:role,' . UserType::Buyer->value, 'nullable', new Enum(BusinessType::class)],

            // riders only
            'category' => ['required_if:role,' . UserType::Rider->value, 'nullable', new Enum(DeliveryCategory::class)],
            'vehicles' => ['required_if:role,' . UserType::Rider->value, 'nullable', 'array', 'min:1'],
            'vehicles.*.vehicle_type' => ['required_with:vehicles', new Enum(VehicleType::class)],
            'vehicles.*.vehicle_no' => ['required_with:vehicles', 'string', 'max:20', 'unique:rider_vehicles,vehicle_no'],
        ];
    }
}
