<?php

namespace App\Models;

use App\Enums\RoleInGame;
use App\Enums\RoleInTeam;
use Illuminate\Database\Eloquent\Model;
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
        'user_id',
        'joined_at',
    ];

    protected $casts = [
        'roleInTeam' => RoleInTeam::class,
        'roleInGame' => RoleInGame::class,
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
