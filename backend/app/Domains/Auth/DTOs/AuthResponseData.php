<?php

namespace App\Domains\Auth\DTOs;

use App\Models\User;

final readonly class AuthResponseData
{
    public function __construct(
        public ?string $token, // token is nullable considering pending verificiations
        public UserData $user,
    ) {}

    public static function fromModel(User $user, ?string $token = null): self
    {
        return new self(token: $token, user: UserData::fromModel($user));
    }
}