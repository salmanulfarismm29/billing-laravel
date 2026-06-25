<?php

namespace App\Models;

use App\Traits\{
    Auditable,
    CascadesSoftDeletes,
    HasActiveScope,
    HasHashedId
};
use Illuminate\Database\Eloquent\{
    Factories\HasFactory,
    Model,
    Relations\BelongsToMany,
    Relations\HasMany,
    Relations\HasOne,
    SoftDeletes
};

class Shop extends Model
{
    use HasFactory, SoftDeletes, Auditable, HasActiveScope, HasHashedId, CascadesSoftDeletes;

    protected $fillable = [
        'name',
        'location',
        'phone',
        'is_active',
    ];

    protected $hidden = [
        'id',
    ];

    protected $appends = [
        'hashed_id',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
    
    public function cascadeDeletes(): array
    {
        return ['users', 'products', 'bills', 'setting'];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function associatedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'shop_user');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class);
    }

    public function setting(): HasOne
    {
        return $this->hasOne(Setting::class);
    }
}
