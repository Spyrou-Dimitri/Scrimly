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
            self::PENDING => __('enums/status-application.pending'),
            self::ACCEPTED => __('enums/status-application.accepted'),
            self::REJECTED => __('enums/status-application.rejected'),
        };
    }
}
