<?php

namespace App\Enums;

enum StatusInvitation: string
{
    case PENDING = 'pending';
    case ACCEPTED = 'accepted';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => __('enums/status-invitation.pending'),
            self::ACCEPTED => __('enums/status-invitation.accepted'),
            self::REJECTED => __('enums/status-invitation.rejected'),
        };
    }
    public function macaron(): string
    {
        return match ($this) {
            self::PENDING => 'bg-gray-500',
            self::ACCEPTED => 'bg-green-500',
            self::REJECTED => 'bg-red-500',
        };
    }
}
