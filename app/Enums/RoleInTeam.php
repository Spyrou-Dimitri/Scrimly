<?php

namespace App\Enums;

enum RoleInTeam: string
{
    case COACH = 'coach';
    case PLAYER = 'player';
    case STAFF = 'staff';

    public function label(): string
    {
        return match ($this) {
            self::COACH => 'Coach',
            self::PLAYER => 'Joueur',
            self::STAFF => 'Staff',
        };
    }
}
