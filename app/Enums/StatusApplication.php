<?php

namespace App\Enums;

enum StatusApplication: string
{
    case PENDING = 'pending';
    case ACCEPTED = 'accepted';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'En attente',
            self::ACCEPTED => 'Accepté',
            self::REJECTED => 'Rejeté',
        };
    }
}
