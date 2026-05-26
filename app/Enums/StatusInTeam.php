<?php

namespace App\Enums;

enum StatusInTeam: string
{
    case ACCEPTED = 'accepted';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::ACCEPTED => __('enums/status-in-team.accepted'),
            self::REJECTED => __('enums/status-in-team.rejected'),
        };
    }
}
