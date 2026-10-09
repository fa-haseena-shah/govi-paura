<?php
namespace App\Enums;

enum PaymentMethod: string
{
    case Card = 'card';
    case CashOnDelivery = 'cash_on_delivery';
}