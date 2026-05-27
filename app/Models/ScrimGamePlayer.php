<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class ScrimGamePlayer extends Model
{
    protected $fillable = [
        'scrim_game_id',
        'team_member_id',
        'champion',
        'kills',
        'deaths',
        'assists',
        'cs',
    ];

    public function scrimGame(): BelongsTo
    {
        return $this->belongsTo(ScrimGame::class);
    }

    public function teamMember(): BelongsTo
    {
        return $this->belongsTo(TeamMember::class);
    }

    public function getGeneralKdaAttribute(): float
    {
        $totalKda = $this->kills + $this->assists;
        $totalDeaths = $this->deaths;
        if ($totalDeaths === 0) {
            return 0;
        }

        return round($totalKda / $totalDeaths, 2);
    }
    public function scrim(): HasOneThrough
    {
        return $this->hasOneThrough(
            Scrim::class,
            ScrimGame::class,
            'id',
            'id',
            'scrim_game_id',
            'scrim_id'
        );
    }
}
