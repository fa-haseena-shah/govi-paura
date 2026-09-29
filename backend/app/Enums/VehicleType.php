<?php

namespace App\Enums;

enum VehicleType: string
{
    case Bike = 'bike';
    case ThreeWheeler = 'three_wheeler';
    case Tractor = 'tractor';
    case Lorry = 'lorry';
}