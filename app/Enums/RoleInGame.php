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
            self::TOP => 'Top',
            self::JUNGLE => 'Jungle',
            self::MID => 'Mid',
            self::ADC => 'ADC',
            self::SUPPORT => 'Support',
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
