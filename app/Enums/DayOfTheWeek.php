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
            self::MONDAY => __('modals/edit-availabilities.monday'),
            self::TUESDAY => __('modals/edit-availabilities.tuesday'),
            self::WEDNESDAY => __('modals/edit-availabilities.wednesday'),
            self::THURSDAY => __('modals/edit-availabilities.thursday'),
            self::FRIDAY => __('modals/edit-availabilities.friday'),
            self::SATURDAY => __('modals/edit-availabilities.saturday'),
            self::SUNDAY => __('modals/edit-availabilities.sunday'),
        };
    }

    public function truncatedLabel(): string
    {
        return match ($this) {
            self::MONDAY => __('modals/edit-availabilities.monday_short'),
            self::TUESDAY => __('modals/edit-availabilities.tuesday_short'),
            self::WEDNESDAY => __('modals/edit-availabilities.wednesday_short'),
            self::THURSDAY => __('modals/edit-availabilities.thursday_short'),
            self::FRIDAY => __('modals/edit-availabilities.friday_short'),
            self::SATURDAY => __('modals/edit-availabilities.saturday_short'),
            self::SUNDAY => __('modals/edit-availabilities.sunday_short'),
        };
    }
}
