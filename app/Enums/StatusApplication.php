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
    public function macaron(): string
    {
        return match ($this) {
            self::PENDING => 'bg-gray-500 py-2 px-4 rounded-full',
            self::ACCEPTED => 'bg-green-800 py-2 px-4 rounded-full',
            self::REJECTED => 'bg-red-700 py-2 px-4 rounded-full',
        };
    }
}
