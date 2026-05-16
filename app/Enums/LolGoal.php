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

    public function macaron(): string
    {
        return match ($this) {
            self::FUN => 'bg-objective-fun/20 text-objective-fun',
            self::TRY_HARD => 'bg-objective-semi-competitive/20 text-objective-semi-competitive',
            self::PROFESSIONAL => 'bg-objective-competitive/20 text-objective-competitive',
        };
    }
}
