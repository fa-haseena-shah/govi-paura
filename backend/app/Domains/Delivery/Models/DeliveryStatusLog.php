<?php

namespace App\Domains\Delivery\Models;

use App\Enums\DeliveryStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One row per status change (table delivery_statuses, primary key = delivery_id + status_timestamp).
 */
class DeliveryStatusLog extends Model
{
    protected $table = 'delivery_statuses';

    // composite key, no auto-increment id, no created_at/updated_at
    protected $primaryKey = null;
    public $incrementing = false;
    public $timestamps = false;

    // the timestamp column is part of the primary key and has microsecond precision;
    // the default 'Y-m-d H:i:s' would drop the microseconds and two quick updates could collide
    protected $dateFormat = 'Y-m-d H:i:s.u';

    protected $fillable = ['delivery_id', 'status_timestamp', 'status', 'note', 'changed_by'];

    protected function casts(): array
    {
        return [
            'status' => DeliveryStatus::class,
            'status_timestamp' => 'datetime',
        ];
    }

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class);
    }
}
