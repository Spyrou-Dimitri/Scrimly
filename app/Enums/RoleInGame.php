<?php

namespace App\Enums;

enum RoleInGame: string
{
    case TOP = 'top';
    case JUNGLE = 'jungle';
    case MID = 'mid';
    case ADC = 'adc';
    case SUPPORT = 'support';

    public function label(): string
    {
        return match ($this) {
            self::TOP => __('enums/role-in-game.top'),
            self::JUNGLE => __('enums/role-in-game.jungle'),
            self::MID => __('enums/role-in-game.mid'),
            self::ADC => __('enums/role-in-game.adc'),
            self::SUPPORT => __('enums/role-in-game.support'),
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::TOP => 'img/Role/top.png',
            self::JUNGLE => 'img/Role/jungle.png',
            self::MID => 'img/Role/mid.png',
            self::ADC => 'img/Role/bot.png',
            self::SUPPORT => 'img/Role/support.png',
        };
    }
}
