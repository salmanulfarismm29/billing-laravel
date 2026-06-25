<?php

namespace App\Enums;

enum Status: int
{
    case INACTIVE = 0;
    case ACTIVE = 1;

    public function label(): string
    {
        return match($this) {
            self::INACTIVE => 'Inactive',
            self::ACTIVE => 'Active',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::INACTIVE => 'danger',
            self::ACTIVE => 'success',
        };
    }

    public static function fromName(string $name): self
    {
        foreach (self::cases() as $case) {
            if ($case->name === strtoupper($name)) {
                return $case;
            }
        }
        throw new \ValueError("$name is not a valid backing name for enum " . self::class);
    }

    public static function fromKey(int $key): self
    {
        return self::from($key);
    }
}
