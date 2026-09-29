<?php

namespace App\Domains\Auth\Models;

use App\Enums\BusinessType;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Buyer extends Model
{
    protected $primaryKey = 'user_id';
    public $incrementing = false;

    protected $fillable = ['user_id', 'business_name', 'business_type'];

    protected function casts(): array
    {
        return ['business_type' => BusinessType::class];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}