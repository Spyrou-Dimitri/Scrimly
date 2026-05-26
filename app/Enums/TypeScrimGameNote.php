<?php

namespace App\Enums;

enum TypeScrimGameNote: string
{
    case POSITIVE = 'positive';
    case NEGATIVE = 'negative';

    public function label(): string
    {
        return match ($this) {
            self::POSITIVE => __('enums/type-scrim-game-note.positive'),
            self::NEGATIVE => __('enums/type-scrim-game-note.negative'),
        };
    }
}
