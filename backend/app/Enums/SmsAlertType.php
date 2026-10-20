<?php
namespace App\Enums;

enum SmsAlertType: string
{
    case NewOrder          = 'new_order';
    case NewBid            = 'new_bid';
    case LowStock          = 'low_stock';
    case DeliveryAtRisk    = 'delivery_at_risk';
    case DeliveryOverdue   = 'delivery_overdue';
}