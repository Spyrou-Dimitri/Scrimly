<?php

namespace App\Models;

use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScrimGame extends Model
{
    protected $fillable = [
        'scrim_id',
        'title',
        'is_victory',
        'opponent_team_members_starters',
        'duration',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'duration' => 'integer',
            'opponent_team_members_starters' => 'array',
        ];
    }

    public function scrim(): BelongsTo
    {
        return $this->belongsTo(Scrim::class);
    }

    public function scrimGamePlayers(): HasMany
    {
        return $this->hasMany(ScrimGamePlayer::class);
    }

    public function scrimGameNotes(): HasMany
    {
        return $this->hasMany(ScrimGameNote::class);
    }

    public function getFormattedDurationAttribute(): string
    {
        return CarbonInterval::seconds($this->duration)->cascade()->format('%I:%S');
    }

    public function calculateKda(int $kills, int $deaths, int $assists): float
    {
        if ($deaths === 0) {
            return 0;
        }

        return round(($kills + $assists) / $deaths, 2);
    }
}
