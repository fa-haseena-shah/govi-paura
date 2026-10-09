<?php

namespace App\Domains\Order\Models;

use App\Domains\Listing\Models\Listing;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = ['order_id', 'listing_id', 'qty', 'unit_price', 'sub_total'];

    protected function casts(): array
    {
        return [
            'qty' => 'integer',
            'unit_price' => 'decimal:2',
            'sub_total' => 'decimal:2',
        ];
    }

    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
    public function listing(): BelongsTo { return $this->belongsTo(Listing::class); }
}
