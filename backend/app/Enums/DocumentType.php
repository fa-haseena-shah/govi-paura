<?php

namespace App\Enums;

enum DocumentType: string
{
    case Nic = 'nic';
    case VehicleRegistration = 'vehicle_registration';
    case DrivingLicense = 'driving_license';

    public function label(): string
    {
        return match ($this) {
            self::Nic => 'National Identity Card (NIC)',
            self::VehicleRegistration => 'Vehicle registration certificate',
            self::DrivingLicense => 'Driving licence',
        };
    }
}
