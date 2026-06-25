<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShopUser extends Pivot
{
    use SoftDeletes, Auditable;

    protected $table = 'shop_user';

    protected $fillable = [
        'user_id',
        'shop_id',
    ];
}
