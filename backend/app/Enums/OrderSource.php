<?php

namespace App\Enums;

enum OrderSource: string
{
    case FixedPricePurchase = 'fixed_price_purchase';
    case AcceptedBid = 'accepted_bid';
}