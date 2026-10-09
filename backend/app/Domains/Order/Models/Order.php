<?php

namespace App\Domains\Order\Models;

use App\Domains\Bidding\Models\Bid;
use App\Domains\Payment\Models\Payment;
use App\Enums\OrderSource;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Order extends Model
{
    protected $fillable = [
        'buyer_id', 'farmer_id', 'bid_id', 'total_amount',
        'status', 'payment_status', 'source',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'payment_status' => PaymentStatus::class,
            'source' => OrderSource::class,
            'total_amount' => 'decimal:2',
        ];
    }

    public function buyer(): BelongsTo { return $this->belongsTo(User::class, 'buyer_id'); }
    public function farmer(): BelongsTo { return $this->belongsTo(User::class, 'farmer_id'); }
    public function bid(): BelongsTo { return $this->belongsTo(Bid::class); }
    public function items(): HasMany { return $this->hasMany(OrderItem::class); }
    public function payments(): MorphMany { return $this->morphMany(Payment::class, 'payable'); }
}
