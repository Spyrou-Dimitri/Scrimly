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
            self::BR => 'Brésil',
            self::EUNE => 'Europe du Nord et de l\'Est',
            self::EUW => 'Europe de l\'Ouest',
            self::JP => 'Japon',
            self::KR => 'Corée du Sud',
            self::LAN => 'Amérique latine Nord',
            self::LAS => 'Amérique latine Sud',
            self::NA => 'Amérique du Nord',
            self::OCE => 'Océanie',
            self::PH => 'Philippines',
            self::RU => 'Russie',
            self::SG => 'Singapour, Malaisie et Indonésie',
            self::TR => 'Turquie',
            self::TW => 'Taïwan, Hong Kong et Macao',
            self::VN => 'Vietnam',
            self::CN => 'Chine',
        };
    }
}
