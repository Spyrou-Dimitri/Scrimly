<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScrimGame extends Model
{
    protected $fillable = [
        'scrim_id',
        'game_number',
        'side_of_your_team',
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

    public function scrimGamePlayers(): HasMany
    {
        return $this->hasMany(ScrimGamePlayer::class);
    }
}
