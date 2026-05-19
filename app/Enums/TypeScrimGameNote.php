<?php

namespace App\Enums;

enum TypeScrimGameNote: string
{
    case POSITIVE = 'positive';
    case NEGATIVE = 'negative';

    public function label(): string
    {
        return match ($this) {
            self::POSITIVE => 'Positive',
            self::NEGATIVE => 'Negative',
        };
    }
}
