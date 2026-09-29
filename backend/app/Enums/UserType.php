<?php

namespace App\Enums;

enum UserType: string
{
    case Farmer = 'farmer';
    case Buyer  = 'buyer';
    case Rider  = 'rider';
    case Admin  = 'admin';
}