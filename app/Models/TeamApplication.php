<?php

namespace App\Models;

use App\Enums\RoleInGame;
use App\Enums\RoleInTeam;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamApplication extends Model
{
    protected $fillable = [
        'team_id',
        'user_id',
        'roleInTeam',
        'roleInGame',
        'motivation',
        'status',
    ];

    protected $casts = [
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
