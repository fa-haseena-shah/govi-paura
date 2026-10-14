<?php

namespace App\Domains\Delivery\Models;

use App\Domains\Auth\Models\Rider;
use App\Domains\Order\Models\Order;
use App\Enums\DeliveryCategory;
use App\Enums\DeliveryStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Delivery extends Model
{
    protected $fillable = [
        'order_id', 'rider_id', 'pickup', 'dropoff', 'status', 'category', 'region',
        'pickup_lat', 'pickup_lng', 'dropoff_lat', 'dropoff_lng', 'distance_km', 'estimated_minutes',
        'deadline_at', 'assigned_at', 'pickup_due_at', 'picked_up_at', 'in_transit_at',
        'delivered_at', 'at_risk_at', 'sla_breached_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => DeliveryStatus::class,
            'category' => DeliveryCategory::class,
            'pickup_lat' => 'float',
            'pickup_lng' => 'float',
            'dropoff_lat' => 'float',
            'dropoff_lng' => 'float',
            'distance_km' => 'float',
            'estimated_minutes' => 'integer',
            'deadline_at' => 'datetime',
            'assigned_at' => 'datetime',
            'pickup_due_at' => 'datetime',
            'picked_up_at' => 'datetime',
            'in_transit_at' => 'datetime',
            'delivered_at' => 'datetime',
            'at_risk_at' => 'datetime',
            'sla_breached_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo { return $this->belongsTo(Order::class); }

    // deliveries.rider_id points at riders.user_id (== users.id)
    public function rider(): BelongsTo { return $this->belongsTo(Rider::class, 'rider_id', 'user_id'); }
    public function riderUser(): BelongsTo { return $this->belongsTo(User::class, 'rider_id'); }

    public function logs(): HasMany
    {
        return $this->hasMany(DeliveryStatusLog::class)->orderBy('status_timestamp');
    }

    /** Whole minutes until the deadline (negative once it has passed). */
    public function minutesRemaining(): ?int
    {
        if ($this->deadline_at === null) {
            return null;
        }

        return intdiv($this->deadline_at->getTimestamp() - now()->getTimestamp(), 60);
    }
}
