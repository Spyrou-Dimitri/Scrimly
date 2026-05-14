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
            self::IONIA => 'Ionia',
            self::NOXUS => 'Noxus',
            self::PILTOVER => 'Piltover',
            self::DEMACIA => 'Demacia',
            self::FRELJORD => 'Freljord',
            self::VOID => 'Void',
        };
    }
}