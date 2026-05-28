<?php

namespace App\Models;

use App\Enums\StatusInvitation;
use App\Enums\RoleInTeam;
use App\Enums\RoleInGame;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class TeamInvitation extends Model
{
    protected $fillable = [
        'team_id',
        'status',
        'roleInTeam',
        'roleInGame',
        'user_id',
    ];

    protected $casts = [
        'status' => StatusInvitation::class,
        'roleInTeam' => RoleInTeam::class,
        'roleInGame' => RoleInGame::class,
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
