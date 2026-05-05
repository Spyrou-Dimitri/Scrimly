<?php

namespace App\Models;

use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiotMatch extends Model
{
    protected $fillable = [
        'riot_profile_id',
        'match_id',
        'game_duration',
        'played_at',
        'champion_name',
        'champion_id',
        'champion_level',
        'role',
        'win',
        'kills',
        'deaths',
        'assists',
        'cs',
        'items',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'win' => 'boolean',
            'items' => 'array',
            'played_at' => 'datetime',
        ];
    }

    public function riotProfile(): BelongsTo
    {
        return $this->belongsTo(RiotProfile::class);
    }

    /**
     * Match KDA: (K+A)/D, or K+A when deaths are zero (perfect KDA convention).
     */
    public function getKda(): float
    {
        if ($this->deaths === 0) {
            return round($this->kills + $this->assists, 2);
        }

        return round(($this->kills + $this->assists) / $this->deaths, 2);
    }

    public function getDurationGameMinutes(): CarbonInterval
    {
        return CarbonInterval::seconds($this->game_duration)->cascade();
    }

    public function getCsPerMinute(): float
    {
        if ($this->game_duration === 0) {
            return 0.0;
        }

        return round(($this->cs * 60) / $this->game_duration, 1);
    }
}
