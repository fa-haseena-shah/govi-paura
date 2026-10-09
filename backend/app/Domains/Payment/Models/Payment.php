<?php

namespace App\Domains\Payment\Models;

use App\Enums\PaymentTransactionStatus;
use App\Enums\PaymentMethod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Payment extends Model
{
    protected $fillable = [
        'reference', 'payable_id', 'payable_type', 'amount',
        'currency', 'status', 'method',
    ];

    protected function casts(): array
    {
        return [
            'status' => PaymentTransactionStatus::class,
            'method' => PaymentMethod::class,
            'amount' => 'decimal:2',
        ];
    }

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }
}

