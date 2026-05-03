<?php

namespace App\Enums;

enum StatusInTeam: string
{
    case ACCEPTED = 'accepted';
    case REJECTED = 'rejected';


    public function label(): string
    {
        return match ($this) {
            self::ACCEPTED => 'Accepté',
            self::REJECTED => 'Rejeté',
        };
    }
}
