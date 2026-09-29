<?php

namespace App\Models;

use App\Domains\Auth\Models\Admin;
use App\Domains\Auth\Models\Buyer;
use App\Domains\Auth\Models\Farmer;
use App\Domains\Auth\Models\Rider;
use App\Enums\Language;
use App\Enums\UserStatus;
use App\Enums\UserType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'password',
        'preferred_lang',
        'role',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password'       => 'hashed',
            'preferred_lang' => Language::class,
            'role'           => UserType::class,
            'status'         => UserStatus::class,
        ];
    }

    public function farmerProfile(): HasOne
    {
        return $this->hasOne(Farmer::class);
    }

    public function buyerProfile(): HasOne
    {
        return $this->hasOne(Buyer::class);
    }

    public function riderProfile(): HasOne
    {
        return $this->hasOne(Rider::class);
    }

    public function adminProfile(): HasOne
    {
        return $this->hasOne(Admin::class);
    }

    public function isFarmer(): bool { return $this->role === UserType::Farmer; }
    public function isBuyer(): bool  { return $this->role === UserType::Buyer; }
    public function isRider(): bool  { return $this->role === UserType::Rider; }
    public function isAdmin(): bool  { return $this->role === UserType::Admin; }

    public function isActive(): bool
    {
        return $this->status === UserStatus::Active;
    }
}