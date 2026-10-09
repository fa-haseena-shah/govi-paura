<?php

namespace App\Domains\Notification\Models;

use App\Enums\SmsAlertType;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmsLog extends Model
{
    protected $fillable = ['user_id', 'phone', 'type', 'message', 'status'];

    protected function casts(): array
    {
        return ['type' => SmsAlertType::class];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}