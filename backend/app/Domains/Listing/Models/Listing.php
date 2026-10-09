<?php

namespace App\Domains\Listing\Models;

use App\Enums\ListingStatus;
use App\Enums\SellingMethod;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Listing extends Model
{
    protected $fillable = [
        'farmer_id', 'crop_type', 'qty', 'price_per_unit', 'harvest_date',
        'location', 'photo_url', 'selling_method', 'starting_price',
        'closing_time', 'status',
    ];

    protected function casts(): array
    {
        return [
            'harvest_date'   => 'date',
            'closing_time'   => 'datetime',
            'selling_method' => SellingMethod::class,
            'status'         => ListingStatus::class,
            'price_per_unit' => 'decimal:2',
            'starting_price' => 'decimal:2',
        ];
    }

    public function farmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'farmer_id');
    }
}