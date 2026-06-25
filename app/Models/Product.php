<?php

namespace App\Models;

use App\Traits\{
    Auditable,
    HasActiveScope,
    HasHashedId
};
use Illuminate\Database\Eloquent\{
    Factories\HasFactory,
    Model,
    Relations\BelongsTo,
    SoftDeletes
};

class Product extends Model
{
    use HasFactory, SoftDeletes, Auditable, HasActiveScope, HasHashedId;

    protected $fillable = [
        'shop_id',
        'name',
        'price',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}
