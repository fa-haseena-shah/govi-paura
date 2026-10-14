<?php

namespace App\Domains\PriceHistory\Models;

use App\Domains\Listing\Models\Listing;
use App\Domains\Order\Models\Order;
use App\Domains\Order\Models\OrderItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceRecord extends Model
{
    protected $fillable = [
        'order_id', 'order_item_id', 'listing_id', 'crop_type', 'region',
        'unit_price', 'qty', 'total_amount', 'source', 'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'qty' => 'integer',
            'recorded_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
    public function orderItem(): BelongsTo { return $this->belongsTo(OrderItem::class); }
    public function listing(): BelongsTo { return $this->belongsTo(Listing::class); }
}
