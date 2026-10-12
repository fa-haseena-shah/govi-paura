<?php

namespace App\Domains\Subscription\Models;

use App\Domains\Auth\Models\Rider;
use App\Domains\Payment\Models\Payment;
use App\Enums\PaymentStatus;
use App\Enums\SubscriptionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Subscription extends Model
{
    protected $fillable = [
        'rider_id', 'type', 'status', 'fee', 'period_months',
        'payment_status', 'start_date', 'end_date', 'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'fee' => 'decimal:2',
            'period_months' => 'integer',
            'status' => SubscriptionStatus::class,
            'payment_status' => PaymentStatus::class,
            'start_date' => 'date',
            'end_date' => 'date',
            'paid_at' => 'datetime',
        ];
    }

    public function rider(): BelongsTo { return $this->belongsTo(Rider::class, 'rider_id', 'user_id'); }
    public function payments(): MorphMany { return $this->morphMany(Payment::class, 'payable'); }
}
