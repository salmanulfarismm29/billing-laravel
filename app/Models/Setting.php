<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\{
    Factories\HasFactory,
    Model,
    Relations\BelongsTo,
    SoftDeletes
};

class Setting extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'shop_id',
        'ask_customer_details',
        'ask_payment_method',
        'billing_calculator_prices',
    ];

    protected function casts(): array
    {
        return [
            'ask_customer_details'     => 'boolean',
            'ask_payment_method'       => 'boolean',
            // Stored as an ordered JSON array of selected price values (e.g. [10, 15, 25])
            'billing_calculator_prices' => 'array',
        ];
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}
