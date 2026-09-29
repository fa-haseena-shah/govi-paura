<?php

namespace App\Domains\Auth\Services;

use App\Domains\Auth\DTOs\AuthResponseData;
use App\Domains\Auth\DTOs\LoginRequestData;
use App\Domains\Auth\DTOs\RegisterRequestData;
use App\Models\User;

interface AuthServiceInterface
{
    public function register(RegisterRequestData $data): AuthResponseData;

    public function login(LoginRequestData $data): AuthResponseData;

    public function logout(User $user): void;
}