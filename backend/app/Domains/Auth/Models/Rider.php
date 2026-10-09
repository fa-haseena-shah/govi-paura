<?php

namespace App\Domains\Auth\Models;

use App\Enums\DeliveryCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rider extends Model
{
    protected $primaryKey = 'user_id';
    public $incrementing = false;

    protected $fillable = ['user_id', 'category', 'region'];

    protected function casts(): array
    {
        return ['category' => DeliveryCategory::class];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(RiderVehicle::class, 'rider_id', 'user_id');
    }
}