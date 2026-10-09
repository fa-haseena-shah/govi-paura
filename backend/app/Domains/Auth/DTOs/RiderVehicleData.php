<?php

namespace App\Domains\Auth\DTOs;

use App\Enums\VehicleType;

final readonly class RiderVehicleData
{
    public function __construct(
        public VehicleType $vehicleType,
        public string $vehicleNo,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            vehicleType: VehicleType::from($data['vehicle_type']),
            vehicleNo: $data['vehicle_no'],
        );
    }
}