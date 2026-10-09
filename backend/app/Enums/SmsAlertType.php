<?php
namespace App\Enums;

enum SmsAlertType: string
{
    case NewOrder = 'new_order';
    case NewBid = 'new_bid';
    case LowStock = 'low_stock';
}