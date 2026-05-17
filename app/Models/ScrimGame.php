<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScrimGame extends Model
{
    protected $fillable = [
        'scrim_id',
        'game_number',
        'winner_team_id',
        'duration_minutes',
        'screenshot',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'game_number' => 'integer',
            'duration_minutes' => 'integer',
        ];
    }

  
    public function scrim(): BelongsTo
    {
        return $this->belongsTo(Scrim::class);
    }

  
    public function winnerTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'winner_team_id');
    }
}
