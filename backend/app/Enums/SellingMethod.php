<?php
namespace App\Enums;

enum SellingMethod: string
{
    case FixedPrice = 'fixed_price';
    case Bidding = 'bidding';
}