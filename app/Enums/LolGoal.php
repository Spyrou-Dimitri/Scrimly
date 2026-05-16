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
            self::FUN => __('enums/lol-goal.fun'),
            self::TRY_HARD => __('enums/lol-goal.try_hard'),
            self::PROFESSIONAL => __('enums/lol-goal.professional'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::FUN => 'text-objective-fun',
            self::TRY_HARD => 'text-objective-semi-competitive',
            self::PROFESSIONAL => 'text-objective-competitive',
        };
    }

    public function macaron(): string
    {
        return match ($this) {
            self::FUN => 'bg-objective-fun/20 '.$this->color(),
            self::TRY_HARD => 'bg-objective-semi-competitive/20 '.$this->color(),
            self::PROFESSIONAL => 'bg-objective-competitive/20 '.$this->color(),
        };
    }
}
