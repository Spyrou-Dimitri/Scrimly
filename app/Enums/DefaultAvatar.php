<?php

namespace App\Enums;

enum DefaultAvatar: string
{
    case CAMILLE = 'Camille';
    case RYZE = 'Ryze';
    case JARVAN = 'Jarvan';
    case YUUMI = 'Yuumi';
    case ALISTAR = 'Alistar';
    case DIANA = 'Diana';

    public function getPath(): string
    {
        return 'img/IconsAvatars/'.$this->value.'.jpg';
    }

    public function url(): string
    {
        return asset($this->getPath());
    }

    public function label(): string
    {
        return match ($this) {
            self::CAMILLE => 'Camille',
            self::RYZE => 'Ryze',
            self::JARVAN => 'Jarvan IV',
            self::ALISTAR => 'Alistar',
            self::DIANA => 'Diana',
            self::YUUMI => 'Yuumi',
        };
    }
}
