<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}
