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
            self::CAMILLE => __('enums/default-avatar.camille'),
            self::RYZE => __('enums/default-avatar.ryze'),
            self::JARVAN => __('enums/default-avatar.jarvan'),
            self::ALISTAR => __('enums/default-avatar.alistar'),
            self::DIANA => __('enums/default-avatar.diana'),
            self::YUUMI => __('enums/default-avatar.yuumi'),
        };
    }
}
