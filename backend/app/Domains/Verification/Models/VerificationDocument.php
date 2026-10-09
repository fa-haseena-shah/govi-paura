<?php

namespace App\Domains\Verification\Models;

use App\Enums\DocumentType;
use App\Enums\VerificationStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerificationDocument extends Model
{
    protected $fillable = [
        'user_id', 'admin_id', 'type', 'url', 'original_name',
        'status', 'rejection_reason', 'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => DocumentType::class,
            'status' => VerificationStatus::class,
            'reviewed_at' => 'datetime',
        ];
    }

    // `url` holds the file's path on the private disk, never a public link
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function reviewer(): BelongsTo { return $this->belongsTo(User::class, 'admin_id'); }
}
