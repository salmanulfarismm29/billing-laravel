<?php

namespace App\Enums;

enum UserRole: int
{
    case ADMIN = 1;
    case MANAGER = 2;
    case CASHIER = 3;

    public function label(): string
    {
        return match($this) {
            self::ADMIN => 'Admin',
            self::MANAGER => 'Manager',
            self::CASHIER => 'Cashier',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::ADMIN => 'danger',
            self::MANAGER => 'warning',
            self::CASHIER => 'success',
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
