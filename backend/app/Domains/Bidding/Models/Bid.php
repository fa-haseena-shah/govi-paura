<?php

namespace App\Domains\Bidding\Models;

use App\Enums\BidStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bid extends Model
{
    protected $fillable = [
        'listing_id',
        'buyer_id',
        'bid_amount',
        'bid_qty',
        'bid_status',
        'rejection_reason',
        'decided_at',
    ];

    protected function casts(): array
    {
        return [
            'bid_amount' => 'decimal:2',
            'bid_qty' => 'decimal:2',
            'bid_status' => BidStatus::class,
            'decided_at' => 'datetime',
        ];
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(config('bidding.listing_model'), 'listing_id');
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }
}
