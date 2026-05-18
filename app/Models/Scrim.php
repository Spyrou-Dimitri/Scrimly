<?php

namespace App\Models;

use App\Enums\StatusScrim;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Scrim extends Model
{
    protected $fillable = [
        'scheduled_date',
        'scheduled_time',
        'number_of_games',
        'status',
        'summary',
        'advantages',
        'disadvantages',
        'scrim_request_id',
        'opponent_team_id',
        'team_id',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'scheduled_time' => 'datetime',
        'number_of_games' => 'integer',
        'status' => StatusScrim::class,
    ];

    public function scrimRequest(): BelongsTo
    {
        return $this->belongsTo(ScrimRequest::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    public function opponentTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'opponent_team_id');
    }

    public function scrimGames(): HasMany
    {
        return $this->hasMany(ScrimGame::class)->orderBy('game_number');
    }
}
