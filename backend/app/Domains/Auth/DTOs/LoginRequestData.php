<?php

namespace App\Domains\Auth\DTOs;

final readonly class LoginRequestData
{
    public function __construct(
        public string $identifier,
        public string $password,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            identifier: $data['identifier'],
            password: $data['password'],
        );
    }
}