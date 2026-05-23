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
    public static function historyCases(): array
    {
        return [
            self::COMPLETED,
            self::ABORTED,
            self::CANCELLED,
        ];
    }

    public function macaron(): string
    {
        return match ($this) {
            self::SCHEDULED => 'text-white rounded-full font-bold bg-white/10 py-2 px-4',
            self::IN_PROGRESS => 'text-white rounded-full font-bold bg-task-in-progress py-2 px-4',
            self::COMPLETED => 'text-[#046143] rounded-full font-bold bg-task-done py-2 px-4',
            self::ABORTED, self::CANCELLED => 'text-white rounded-full font-bold bg-red-950/70 py-2 px-4',
        };
    }
}
