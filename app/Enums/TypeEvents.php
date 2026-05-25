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
            self::MEETING => '#6366F1',
            self::TOURNAMENT => '#F97316',
            self::BREAK => '#10B981',
            self::OTHER => '#378ADD',
        };
    }
}
