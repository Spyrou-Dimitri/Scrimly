<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\CarbonInterval;

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
    public function getKda()
    {
        return round(($this->kills + $this->assists) / $this->deaths, 2);
    }
    public function getDurationGameMinutes() 
    {
        return CarbonInterval::seconds($this->game_duration)->cascade();


    }
    public function getCsPerMinute()
    {
        $csPerSeconds = $this->cs * 60;
        return round($csPerSeconds / $this->game_duration, 1);

    }

    
}
