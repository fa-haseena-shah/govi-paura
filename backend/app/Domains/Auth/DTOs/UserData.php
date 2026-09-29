<?php

namespace App\Domains\Auth\DTOs;

use App\Enums\Language;
use App\Enums\UserStatus;
use App\Enums\UserType;
use App\Models\User;

final readonly class UserData
{
    public function __construct(
        public int $id,
        public string $fullName,
        public ?string $email,
        public string $phone,
        public Language $preferredLang,
        public UserType $role,
        public UserStatus $status,
    ) {}

    public static function fromModel(User $user): self
    {
        return new self(
            id: $user->id,
            fullName: $user->full_name,
            email: $user->email,
            phone: $user->phone,
            preferredLang: $user->preferred_lang,
            role: $user->role,
            status: $user->status,
        );
    }
}