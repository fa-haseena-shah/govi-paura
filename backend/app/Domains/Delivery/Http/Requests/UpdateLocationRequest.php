<?php

namespace App\Domains\Delivery\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLocationRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        [$latMin, $latMax] = config('delivery.bounds.lat');
        [$lngMin, $lngMax] = config('delivery.bounds.lng');

        return [
            'address' => ['required', 'string', 'max:500'],
            'region' => ['required', 'string', 'max:255'],
            // inside Sri Lanka, so a swapped lat/lng or a typo is caught early
            'latitude' => ['required', 'numeric', "between:{$latMin},{$latMax}"],
            'longitude' => ['required', 'numeric', "between:{$lngMin},{$lngMax}"],
        ];
    }

    public function messages(): array
    {
        return [
            'latitude.between' => 'The latitude must be inside Sri Lanka.',
            'longitude.between' => 'The longitude must be inside Sri Lanka.',
        ];
    }
}
