<?php

namespace App\Enums;

enum PaymentMethod: int
{
    case CASH = 1;
    case CARD = 2;
    case UPI = 3;

    public function label(): string
    {
        return match ($this) {
            self::CASH => 'Cash',
            self::CARD => 'Card',
            self::UPI => 'UPI',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::CASH => 'success',
            self::CARD => 'primary',
            self::UPI => 'warning',
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
