<?php

namespace App\Enums;

enum DeliveryStatus: string
{
    case Pending = 'pending';       // open opportunity, no rider yet
    case Assigned = 'assigned';
    case PickedUp = 'picked_up';
    case InTransit = 'in_transit';
    case Delivered = 'delivered';

    /** The only status a rider may move to from this one (null = none). */
    public function next(): ?self
    {
        return match ($this) {
            self::Assigned => self::PickedUp,
            self::PickedUp => self::InTransit,
            self::InTransit => self::Delivered,
            default => null,
        };
    }

    /** Held by a rider and not finished yet. */
    public function isActive(): bool
    {
        return in_array($this, [self::Assigned, self::PickedUp, self::InTransit], true);
    }

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Waiting for a rider',
            self::Assigned => 'Assigned',
            self::PickedUp => 'Picked Up',
            self::InTransit => 'In Transit',
            self::Delivered => 'Delivered',
        };
    }

    /** @return self[] */
    public static function active(): array
    {
        return [self::Assigned, self::PickedUp, self::InTransit];
    }
}
