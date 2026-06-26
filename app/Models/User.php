<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Traits\{
    Auditable,
    CascadesSoftDeletes,
    FiltersByEnum,
    HasActiveScope,
    HasHashedId
};
use Illuminate\Database\Eloquent\{
    Factories\HasFactory,
    Relations\BelongsTo,
    Relations\BelongsToMany,
    Relations\HasMany,
    SoftDeletes
};
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, SoftDeletes, Auditable, HasActiveScope, HasHashedId, FiltersByEnum, CascadesSoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'shop_id',
        'is_active',
    ];

    protected $hidden = [
        'id',
        'password',
        'remember_token',
    ];

    protected $appends = [
        'hashed_id',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
        ];
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
    
    public function cascadeDeletes(): array
    {
        return ['bills'];
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function shops(): BelongsToMany
    {
        return $this->belongsToMany(Shop::class, 'shop_user');
    }

    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class, 'cashier_id');
    }
}
