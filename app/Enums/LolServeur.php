<?php

namespace App\Enums;

enum LolServeur: string
{
    case BR = 'BR';   // Brésil
    case EUNE = 'EUNE'; // Europe du Nord et de l'Est
    case EUW = 'EUW';  // Europe de l'Ouest
    case JP = 'JP';   // Japon
    case KR = 'KR';   // Corée du Sud
    case LAN = 'LAN';  // Amérique latine Nord
    case LAS = 'LAS';  // Amérique latine Sud
    case NA = 'NA';   // Amérique du Nord
    case OCE = 'OCE';  // Océanie
    case PH = 'PH';   // Philippines
    case RU = 'RU';   // Russie
    case SG = 'SG';   // Singapour, Malaisie et Indonésie
    case TR = 'TR';   // Turquie
    case TW = 'TW';   // Taïwan, Hong Kong et Macao
    case VN = 'VN';   // Vietnam
    case CN = 'CN';   // Chine (Tencent)

    public function label(): string
    {
        return match ($this) {
            self::BR => __('enums/lol-serveur.br'),
            self::EUNE => __('enums/lol-serveur.eune'),
            self::EUW => __('enums/lol-serveur.euw'),
            self::JP => __('enums/lol-serveur.jp'),
            self::KR => __('enums/lol-serveur.kr'),
            self::LAN => __('enums/lol-serveur.lan'),
            self::LAS => __('enums/lol-serveur.las'),
            self::NA => __('enums/lol-serveur.na'),
            self::OCE => __('enums/lol-serveur.oce'),
            self::PH => __('enums/lol-serveur.ph'),
            self::RU => __('enums/lol-serveur.ru'),
            self::SG => __('enums/lol-serveur.sg'),
            self::TR => __('enums/lol-serveur.tr'),
            self::TW => __('enums/lol-serveur.tw'),
            self::VN => __('enums/lol-serveur.vn'),
            self::CN => __('enums/lol-serveur.cn'),
        };
    }

    public function tagColorVariable(): string
    {
        return '--color-tag-server';
    }

    public function color(): string
    {
        return 'text-tag-server';
    }
}
