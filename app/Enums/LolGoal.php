<?php

namespace App\Enums;

enum LolGoal: string
{
    case FUN = 'fun';
    case TRY_HARD = 'Try Hard';
    case PROFESSIONAL = 'Professional';

    public function label(): string
    {
        return match ($this) {
            self::FUN => 'Fun',
            self::TRY_HARD => 'Try Hard',
            self::PROFESSIONAL => 'Professional',
        };
    }
}
