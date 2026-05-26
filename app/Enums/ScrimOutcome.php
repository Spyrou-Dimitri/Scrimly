<?php

namespace App\Enums;

enum ScrimOutcome: string
{
    case Victory = 'victory';
    case Defeat = 'defeat';
    case Draw = 'draw';

    public static function fromCounts(int $wins, int $losses): ?self
    {
        if ($wins === 0 && $losses === 0) {
            return null;
        }

        return match (true) {
            $wins > $losses => self::Victory,
            $wins < $losses => self::Defeat,
            default => self::Draw,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Victory => __('enums/scrim-outcome.victory'),
            self::Defeat => __('enums/scrim-outcome.defeat'),
            self::Draw => __('enums/scrim-outcome.draw'),
        };
    }

    public function macaron(): string
    {
        return match ($this) {
            self::Victory => 'inline-flex w-fit bg-victory/15 px-3 py-1 text-sm font-bold text-victory',
            self::Defeat => 'inline-flex w-fit bg-defeat/15 px-3 py-1 text-sm font-bold text-defeat',
            self::Draw => 'inline-flex w-fit bg-white/10 px-3 py-1 text-sm font-bold text-text-secondary',
        };
    }
}
