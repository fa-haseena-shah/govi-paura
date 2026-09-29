<?php

namespace App\Domains\Auth\DTOs;

use App\Enums\BusinessType;
use App\Enums\Language;
use App\Enums\DeliveryCategory;
use App\Enums\UserType;

final readonly class RegisterRequestData
{
    public function __construct(
        public string $fullName,
        public ?string $email,
        public string $phone,
        public string $password,
        public Language $preferredLang,
        public UserType $role,

        // farmer only
        public ?string $nic = null,
        public ?string $address = null,
        public ?string $region = null,

        // buyer only
        public ?string $businessName = null,
        public ?BusinessType $businessType = null,

        // rider only
        public ?DeliveryCategory $category = null,
        /** @var RiderVehicleData[] */
        public array $vehicles = [],
    ) {}


    // creating the shape of the object based on already 
    // validated data array $data validted by FormRequest per role
    public static function fromArray(array $data): self
    {
        return new self(
            fullName: $data['full_name'],
            email: $data['email'] ?? null,
            phone: $data['phone'],
            password: $data['password'],
            preferredLang: Language::from($data['preferred_lang']),
            role: UserType::from($data['role']),

            nic: $data['nic'] ?? null,
            address: $data['address'] ?? null,
            region: $data['region'] ?? null,

            businessName: $data['business_name'] ?? null,
            businessType: isset($data['business_type']) ? BusinessType::from($data['business_type']) : null,

            category: isset($data['category']) ? DeliveryCategory::from($data['category']) : null,
            vehicles: array_map(
                fn (array $vehicle) => RiderVehicleData::fromArray($vehicle),
                $data['vehicles'] ?? [],
            ),
        );
    }
}