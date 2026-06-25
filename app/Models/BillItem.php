<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\{
    Factories\HasFactory,
    Model,
    Relations\BelongsTo,
    SoftDeletes
};

class BillItem extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'bill_id',
        'product_id',
        'price_at_time_of_sale',
        'quantity',
    ];

    protected function casts(): array
    {
        return [
            'price_at_time_of_sale' => 'decimal:2',
            'quantity' => 'integer',
        ];
    }

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
