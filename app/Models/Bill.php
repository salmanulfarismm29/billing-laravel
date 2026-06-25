<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Traits\{
    Auditable,
    CascadesSoftDeletes,
    FiltersByEnum,
    HasHashedId
};
use Illuminate\Database\Eloquent\{
    Factories\HasFactory,
    Model,
    Relations\BelongsTo,
    Relations\HasMany,
    SoftDeletes
};

class Bill extends Model
{
    use HasFactory, SoftDeletes, Auditable, HasHashedId, FiltersByEnum, CascadesSoftDeletes;

    protected $fillable = [
        'shop_id',
        'cashier_id',
        'bill_number',
        'total',
        'payment_method',
        'customer_name',
        'customer_phone',
        'qr_code',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'payment_method' => PaymentMethod::class,
        ];
    }
    
    protected static function boot(): void
    {
        parent::boot();
    }

    public function cascadeDeletes(): array
    {
        return ['items'];
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(BillItem::class);
    }
}
