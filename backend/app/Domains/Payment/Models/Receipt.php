<?php

namespace App\Domains\Payment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Receipt extends Model
{
    protected $fillable = ['payment_id', 'receipt_number', 'amount', 'issued_at'];

    protected function casts(): array
    {
        return ['issued_at' => 'datetime', 'amount' => 'decimal:2'];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}