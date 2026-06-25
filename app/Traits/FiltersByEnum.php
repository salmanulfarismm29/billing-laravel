<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait FiltersByEnum
{
    /**
     * Scope a query to filter by an Enum value.
     */
    public function scopeFilterByEnum(Builder $query, string $column, string $enumClass, mixed $value): Builder
    {
        if ($value instanceof $enumClass) {
            return $query->where($column, $value->value);
        }

        // If it's the raw primitive value (string/int)
        if ($enumClass::tryFrom($value)) {
            return $query->where($column, $value);
        }

        return $query;
    }
}
