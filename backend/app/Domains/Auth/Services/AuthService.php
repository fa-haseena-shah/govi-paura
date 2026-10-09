<?php

namespace App\Domains\Auth\Services;

use App\Domains\Auth\DTOs\AuthResponseData;
use App\Domains\Auth\DTOs\LoginRequestData;
use App\Domains\Auth\DTOs\RegisterRequestData;
use App\Domains\Auth\Models\Buyer;
use App\Domains\Auth\Models\Farmer;
use App\Domains\Auth\Models\Rider;
use App\Domains\Auth\Models\RiderVehicle;
use App\Enums\UserStatus;
use App\Enums\UserType;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;
use RuntimeException;

class AuthService implements AuthServiceInterface
{
    public function register(RegisterRequestData $data): AuthResponseData
    {
        if ($data->role === UserType::Admin) {
            throw new InvalidArgumentException('Admins cannot self-register.');
        }

        if ($data->email !== null && User::where('email', $data->email)->exists()) {
            throw new RuntimeException('Email is already registered.');
        }

        // validate role-specific fields so invalid requests dont go to db
        $this->assertRoleFieldsPresent($data);

        return DB::transaction(function () use ($data) {
            $user = User::create([
                'full_name' => $data->fullName,
                'email' => $data->email,
                'phone' => $data->phone,
                'password' => Hash::make($data->password),
                'preferred_lang' => $data->preferredLang,
                'role' => $data->role,
                'status' => match ($data->role) {
                    UserType::Buyer => UserStatus::Active,
                    UserType::Farmer => UserStatus::PendingVerification,
                    UserType::Rider => UserStatus::PendingVerification,
                },
            ]);

            match ($data->role) {
                UserType::Farmer => $this->createFarmerProfile($user, $data),
                UserType::Buyer  => $this->createBuyerProfile($user, $data),
                UserType::Rider  => $this->createRiderProfile($user, $data),
            };

            $token = $user->status === UserStatus::Active
                ? $user->createToken('api-token')->plainTextToken
                : null;

            return AuthResponseData::fromModel($user->fresh(), $token);
        });
    }

    public function login(LoginRequestData $data): AuthResponseData
    {
        $user = User::where('email', $data->identifier)
            ->orWhere('phone', $data->identifier)
            ->first();

        if ($user === null || !Hash::check($data->password, $user->password)) {
            throw new AuthenticationException('Invalid credentials.');
        }

        // Pending and rejected farmers/riders may sign in so they can upload their documents and track
        // verification. The EnsureAccountActive middleware keeps them away from everything else.
        if (in_array($user->status, [UserStatus::Suspended, UserStatus::Deleted], true)) {
            throw new RuntimeException(match ($user->status) {
                UserStatus::Suspended => 'Your account has been suspended. Contact support.',
                default => 'This account no longer exists.',
            });
        }

        // provide token
        $token = $user->createToken('api-token')->plainTextToken;

        return AuthResponseData::fromModel($user, $token);
    }

    public function logout(User $user): void
    {
        // delete token
        $user->currentAccessToken()->delete();
    }

    // check that each role has the necessary fields filled
    private function assertRoleFieldsPresent(RegisterRequestData $data): void
    {
        match ($data->role) {
            UserType::Farmer => (!$data->nic || !$data->address)
                ? throw new InvalidArgumentException('NIC and address are required for farmer registration.')
                : null,
            UserType::Buyer => (!$data->businessName || !$data->businessType)
                ? throw new InvalidArgumentException('Business name and type are required for buyer registration.')
                : null,
            UserType::Rider => (!$data->category || count($data->vehicles) === 0)
                ? throw new InvalidArgumentException('Produce category and at least one vehicle are required for rider registration.')
                : null,
        };
    }

    private function createFarmerProfile(User $user, RegisterRequestData $data): void
    {
        Farmer::create([
            'user_id' => $user->id,
            'nic' => $data->nic,
            'address' => $data->address,
            'region' => $data->region,
        ]);
    }

    private function createBuyerProfile(User $user, RegisterRequestData $data): void
    {
        Buyer::create([
            'user_id' => $user->id,
            'business_name' => $data->businessName,
            'business_type' => $data->businessType,
        ]);
    }

    private function createRiderProfile(User $user, RegisterRequestData $data): void
    {
        $profile = Rider::create([
            'user_id' => $user->id,
            'category' => $data->category,
            'region' => $data->region,
        ]);

        foreach ($data->vehicles as $vehicle) {
            RiderVehicle::create([
                'rider_id' => $profile->user_id,
                'vehicle_type' => $vehicle->vehicleType,
                'vehicle_no' => $vehicle->vehicleNo,
            ]);
        }
    }
}