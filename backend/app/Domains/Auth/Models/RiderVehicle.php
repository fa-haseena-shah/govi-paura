<?php

namespace App\Domains\Auth\Models;

use App\Enums\VehicleType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiderVehicle extends Model
{
    protected $fillable = ['rider_id', 'vehicle_type', 'vehicle_no'];

    protected function casts(): array
    {
        return ['vehicle_type' => VehicleType::class];
    }

    public function rider(): BelongsTo
    {
        return $this->belongsTo(Rider::class, 'rider_id', 'user_id');
    }
}