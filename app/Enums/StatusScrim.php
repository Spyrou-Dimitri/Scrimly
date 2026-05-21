<?php

namespace App\Enums;

enum StatusScrim: string
{
    case SCHEDULED = 'upcoming';
    case IN_PROGRESS = 'in_progress';
    case ABORTED = 'aborted';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::SCHEDULED => 'Planifié',
            self::IN_PROGRESS => 'En cours',
            self::ABORTED => 'Annulé',
            self::COMPLETED => 'Terminé',
            self::CANCELLED => 'Annulé',
        };
    }
}
