<?php

namespace App\Enums;

enum LolTier: string
{
    case IRON = 'IRON';
    case BRONZE = 'BRONZE';
    case SILVER = 'SILVER';
    case GOLD = 'GOLD';
    case PLATINUM = 'PLATINUM';
    case EMERALD = 'EMERALD';
    case DIAMOND = 'DIAMOND';
    case MASTER = 'MASTER';
    case GRANDMASTER = 'GRANDMASTER';
    case CHALLENGER = 'CHALLENGER';

    public function label(): string
    {
        return match ($this) {
            self::IRON => 'Iron',
            self::BRONZE => 'Bronze',
            self::SILVER => 'Silver',
            self::GOLD => 'Gold',
            self::PLATINUM => 'Platinum',
            self::EMERALD => 'Emerald',
            self::DIAMOND => 'Diamond',
            self::MASTER => 'Master',
            self::GRANDMASTER => 'Grandmaster',
            self::CHALLENGER => 'Challenger',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::IRON => '#848484',
            self::BRONZE => '#CD7F32',
            self::SILVER => '#C0C0C0',
            self::GOLD => '#FFD700',
            self::PLATINUM => '#0ACDBE',
            self::EMERALD => '#50C878',
            self::DIAMOND => '#576BCE',
            self::MASTER => '#9D48E0',
            self::GRANDMASTER => '#EF3F3F',
            self::CHALLENGER => '#F4C874',
        };
    }

    public function isApex(): bool
    {
        return in_array($this, [
            self::MASTER,
            self::GRANDMASTER,
            self::CHALLENGER,
        ]);
    }

    public function icon(): string
    {
        return match ($this) {
            self::IRON => 'img/LolTier/Iron.png',
            self::BRONZE => 'img/LolTier/Bronze.png',
            self::SILVER => 'img/LolTier/Silver.png',
            self::GOLD => 'img/LolTier/Gold.png',
            self::PLATINUM => 'img/LolTier/Platinum.png',
            self::EMERALD => 'img/LolTier/Emerald.png',
            self::DIAMOND => 'img/LolTier/Diamond.png',
            self::MASTER => 'img/LolTier/Master.png',
            self::GRANDMASTER => 'img/LolTier/Grandmaster.png',
            self::CHALLENGER => 'img/LolTier/Challenger.png',
        };
    }
}
