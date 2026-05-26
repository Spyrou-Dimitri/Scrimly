<?php

namespace App\Enums;

enum DayOfTheWeek: int
{
    case MONDAY = 0;
    case TUESDAY = 1;
    case WEDNESDAY = 2;
    case THURSDAY = 3;
    case FRIDAY = 4;
    case SATURDAY = 5;
    case SUNDAY = 6;

    public function label(): string
    {
        return match ($this) {
            self::MONDAY => __('enums/day-of-the-week.monday'),
            self::TUESDAY => __('enums/day-of-the-week.tuesday'),
            self::WEDNESDAY => __('enums/day-of-the-week.wednesday'),
            self::THURSDAY => __('enums/day-of-the-week.thursday'),
            self::FRIDAY => __('enums/day-of-the-week.friday'),
            self::SATURDAY => __('enums/day-of-the-week.saturday'),
            self::SUNDAY => __('enums/day-of-the-week.sunday'),
        };
    }

    public function truncatedLabel(): string
    {
        return match ($this) {
            self::MONDAY => __('enums/day-of-the-week.monday_short'),
            self::TUESDAY => __('enums/day-of-the-week.tuesday_short'),
            self::WEDNESDAY => __('enums/day-of-the-week.wednesday_short'),
            self::THURSDAY => __('enums/day-of-the-week.thursday_short'),
            self::FRIDAY => __('enums/day-of-the-week.friday_short'),
            self::SATURDAY => __('enums/day-of-the-week.saturday_short'),
            self::SUNDAY => __('enums/day-of-the-week.sunday_short'),
        };
    }
}
