<?php

namespace App\Enums;

enum StatusScrimRequest: string
{
    case PENDING = 'pending';
    case ACCEPTED = 'accepted';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => __('enums/status-scrim-request.pending'),
            self::ACCEPTED => __('enums/status-scrim-request.accepted'),
            self::REJECTED => __('enums/status-scrim-request.rejected'),
        };
    }
}
