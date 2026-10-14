<?php

namespace App\Domains\Rating\Models;

use App\Domains\Order\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rating extends Model
{
    protected $fillable = ['order_id', 'rater_id', 'ratee_id', 'score', 'comment'];

    protected function casts(): array
    {
        return ['score' => 'integer'];
    }

    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
    public function rater(): BelongsTo { return $this->belongsTo(User::class, 'rater_id'); }
    public function ratee(): BelongsTo { return $this->belongsTo(User::class, 'ratee_id'); }
}
