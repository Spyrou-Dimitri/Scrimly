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
}