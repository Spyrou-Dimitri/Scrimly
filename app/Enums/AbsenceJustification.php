<?php

namespace App\Enums;

enum AbsenceJustification: string
{
    case MEDICAL = 'medical';
    case EXAM = 'exam';
    case VACATION = 'vacation';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::MEDICAL => 'Médical',
            self::EXAM => 'Examen',
            self::VACATION => 'Vacances',
            self::OTHER => 'Autre',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::MEDICAL => 'text-absence-medical',
            self::EXAM => 'text-absence-exam',
            self::VACATION => 'text-absence-vacation',
            self::OTHER => 'text-absence-other',
        };
    }
}
