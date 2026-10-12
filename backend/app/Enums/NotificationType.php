<?php

namespace App\Enums;

enum NotificationType: string
{
    case BidPlaced = 'bid_placed';
    case BidRejected = 'bid_rejected';
    case BiddingClosed = 'bidding_closed';
    case OrderCreated = 'order_created';
    case OrderStatusUpdated = 'order_status_updated';
    case PaymentCompleted = 'payment_completed';
    case LowStock = 'low_stock';
    case DeliveryStatusUpdated = 'delivery_status_updated';
    case ListingRemoved = 'listing_removed';
    case AdminAction = 'admin_action';
    case SubscriptionPaid = 'subscription_paid';
    case SubscriptionExpiring = 'subscription_expiring';
    case SubscriptionExpired = 'subscription_expired';
}
