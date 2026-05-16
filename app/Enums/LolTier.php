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
            self::PLATINUM => 'img/LolTier/Platinium.png',
            self::EMERALD => 'img/LolTier/Emerald.png',
            self::DIAMOND => 'img/LolTier/Diamond.png',
            self::MASTER => 'img/LolTier/Master.png',
            self::GRANDMASTER => 'img/LolTier/Grandmaster.png',
            self::CHALLENGER => 'img/LolTier/Challenger.png',
        };
    }

    public function numericValue(): int
    {
        return match ($this) {
            self::IRON => 0,
            self::BRONZE => 1,
            self::SILVER => 2,
            self::GOLD => 3,
            self::PLATINUM => 4,
            self::EMERALD => 5,
            self::DIAMOND => 6,
            self::MASTER => 7,
            self::GRANDMASTER => 8,
            self::CHALLENGER => 9,
        };
    }
    public function starterAverageEloInterval(): array
    {
        return match ($this) {
            self::IRON => ['minInclusive' => 0, 'maxExclusive' => 400],
            self::BRONZE => ['minInclusive' => 400, 'maxExclusive' => 800],
            self::SILVER => ['minInclusive' => 800, 'maxExclusive' => 1200],
            self::GOLD => ['minInclusive' => 1200, 'maxExclusive' => 1600],
            self::PLATINUM => ['minInclusive' => 1600, 'maxExclusive' => 2000],
            self::EMERALD => ['minInclusive' => 2000, 'maxExclusive' => 2400],
            self::DIAMOND => ['minInclusive' => 2400, 'maxExclusive' => 2800],
            self::MASTER => ['minInclusive' => 2800, 'maxExclusive' => 3200],
            self::GRANDMASTER => ['minInclusive' => 3200, 'maxExclusive' => 3600],
            self::CHALLENGER => ['minInclusive' => 3600, 'maxExclusive' => null],
        };
    }


    public static function getIconAndLabelForAverageEloScoreForTeam(int $value): array
    {
        foreach (self::cases() as $tier) {
            ['minInclusive' => $minInclusive, 'maxExclusive' => $maxExclusive] = $tier->starterAverageEloInterval();

            if ($value >= $minInclusive && ($maxExclusive === null || $value < $maxExclusive)) {
                return [
                    'icon' => $tier->icon(),
                    'label' => $tier->label(),
                ];
            }
        }

        return [
            'icon' => self::CHALLENGER->icon(),
            'label' => self::CHALLENGER->label(),
        ];
    }

    public static function fromNumericValue(?int $averageEloScore): ?array
    {
        if ($averageEloScore === null) {
            return null;
        }

        return self::getIconAndLabelForAverageEloScoreForTeam($averageEloScore);
    }
}
