<?php

namespace App\Domains\Auth\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Farmer extends Model
{
    protected $primaryKey = 'user_id';
    public $incrementing = false;

    protected $fillable = ['user_id', 'nic', 'address', 'region'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}