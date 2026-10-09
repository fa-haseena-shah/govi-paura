<?php

namespace App\Enums;

enum BusinessType: string
{
    case Retailer = 'retailer';
    case Restaurant = 'restaurant';
    case Other = 'other';
}