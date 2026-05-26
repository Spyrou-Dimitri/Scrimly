<?php

namespace App\Enums;

enum TypeEvents: string
{
    case MEETING = 'meeting';
    case TOURNAMENT = 'tournament';
    case BREAK = 'break';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::MEETING => __('enums/type-events.meeting'),
            self::TOURNAMENT => __('enums/type-events.tournament'),
            self::BREAK => __('enums/type-events.break'),
            self::OTHER => __('enums/type-events.other'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::MEETING => '#4F46E5',
            self::TOURNAMENT => '#C2410C',
            self::BREAK => '#047857',
            self::OTHER => '#2563EB',
        };
    }
}
