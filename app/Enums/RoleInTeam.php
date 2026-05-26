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
            self::COACH => __('enums/role-in-team.coach'),
            self::PLAYER => __('enums/role-in-team.player'),
            self::STAFF => __('enums/role-in-team.staff'),
        };
    }
}
