<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\{
    Auditable,
    HasActiveScope,
    HasHashedId
};
use Illuminate\Database\Eloquent\{
    Factories\HasFactory,
    Model,
    Relations\HasMany,
    SoftDeletes
};

class Category extends Model
{
    use HasFactory, SoftDeletes, Auditable, HasActiveScope, HasHashedId;

    protected $fillable = [
        'name',
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

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
