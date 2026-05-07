<?php

namespace App\Models;

use App\Enums\RoleInGame;
use App\Enums\RoleInTeam;
use App\Enums\StatusInTeam;
use App\Observers\TeamMemberObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy(TeamMemberObserver::class)]
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
        'status' => StatusInTeam::class,
        'message' => 'string',
        'joined_at' => 'datetime',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function playerDefaultSchedules(): HasMany
    {
        return $this->hasMany(PlayerDefaultSchedule::class);
    }

    public function absences(): HasMany
    {
        return $this->hasMany(Absence::class);
    }
}
