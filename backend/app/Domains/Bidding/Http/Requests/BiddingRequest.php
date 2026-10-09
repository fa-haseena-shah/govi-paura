<?php

namespace App\Domains\Bidding\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Base for every Bidding request. authorize() answers only
 * "does this route apply to me?" — i.e. is the caller the right role.
 * (A failed check is rendered by Laravel as a 403 JSON response.)
 */
abstract class BiddingRequest extends FormRequest
{
    /** @return list<string> role values allowed to use the route */
    abstract protected function allowedRoles(): array;

    public function authorize(): bool
    {
        $role = $this->user()?->role;
        $value = $role instanceof \BackedEnum ? $role->value : $role;

        return in_array($value, $this->allowedRoles(), true);
    }

    public function rules(): array
    {
        return [];
    }
}
