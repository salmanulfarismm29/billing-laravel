<?php

namespace App\Traits;

use Vinkla\Hashids\Facades\Hashids;

trait HasHashedId
{
    /**
     * Get the hashed version of the model's ID.
     */
    public function getHashedIdAttribute(): string
    {
        return Hashids::encode($this->id);
    }

    /**
     * Resolve a hashed ID back to its numeric ID.
     */
    public static function resolveHashedId(string $hash): ?int
    {
        $decoded = Hashids::decode($hash);
        return $decoded[0] ?? null;
    }
}
