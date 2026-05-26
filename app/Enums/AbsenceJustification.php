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
            self::MEDICAL => __('enums/absence-justification.medical'),
            self::EXAM => __('enums/absence-justification.exam'),
            self::VACATION => __('enums/absence-justification.vacation'),
            self::OTHER => __('enums/absence-justification.other'),
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
