<?php

namespace App\Enums;

enum DefaultTeam: string
{
    case IONIA = 'Ionia';
    case NOXUS = 'Noxus';
    case PILTOVER = 'Piltover';
    case DEMACIA = 'Demacia';
    case FRELJORD = 'Freljord';
    case VOID = 'Void';

    public function getPath(): string
    {
        return 'img/IconsTeams/'.$this->value.'.webp';
    }

    public function url(): string
    {
        return asset($this->getPath());
    }

    public function label(): string
    {
        return match ($this) {
            self::IONIA => __('enums/default-team.ionia'),
            self::NOXUS => __('enums/default-team.noxus'),
            self::PILTOVER => __('enums/default-team.piltover'),
            self::DEMACIA => __('enums/default-team.demacia'),
            self::FRELJORD => __('enums/default-team.freljord'),
            self::VOID => __('enums/default-team.void'),
        };
    }
}
