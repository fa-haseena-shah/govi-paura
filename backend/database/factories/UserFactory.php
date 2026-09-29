<?php

namespace Database\Factories;

use App\Enums\Language;
use App\Enums\UserStatus;
use App\Enums\UserType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'full_name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->numerify('07########'),
            'password' => static::$password ??= Hash::make('password'),
            'preferred_lang' => Language::English,
            'role' => UserType::Buyer,
            'status' => UserStatus::Active,
            'remember_token' => Str::random(10),
        ];
    }

    public function withRole(UserType $role): static
    {
        return $this->state(fn () => ['role' => $role]);
    }
}