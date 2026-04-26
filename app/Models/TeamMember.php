<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\RoleInTeam;
use App\Enums\RoleInGame;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamMember extends Model
{
    protected $fillable = [
        'roleInTeam',
        'roleInGame',
        'is_starter',
        'status',
        'message',
        'team_id',
        'creator_id',
    ];

    protected $casts = [
        'roleInTeam' => RoleInTeam::class,
        'roleInGame' => RoleInGame::class,
        'roleInTeam' => RoleInTeam::class,
        'is_starter' => 'boolean',
        'status' => 'string',
        'message' => 'string',
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
