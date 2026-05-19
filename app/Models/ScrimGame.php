<?php

namespace App\Models;

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
}
