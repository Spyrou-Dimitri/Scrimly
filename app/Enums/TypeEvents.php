<?php

namespace App\Enums;

enum TypeEvents: string
{
    case MEETING = 'meeting';
    case TOURNAMENT = 'tournament';
    case BREAK = 'break';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::MEETING => 'Rencontre Fan',
            self::TOURNAMENT => 'Tournoi',
            self::BREAK => 'Pause',
            self::OTHER => 'Autre',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::MEETING => '#4F46E5',
            self::TOURNAMENT => '#C2410C',
            self::BREAK => '#047857',
            self::OTHER => '#2563EB',
        };
    }
}
