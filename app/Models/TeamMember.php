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
use App\Enums\StatusScrim;

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

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function scrimGamePlayers(): HasMany
    {
        return $this->hasMany(ScrimGamePlayer::class);
    }

    public function isCoachOrStaff(): bool
    {
        return $this->roleInTeam === RoleInTeam::COACH || $this->roleInTeam === RoleInTeam::STAFF || $this->team->creator_id === $this->user_id;
    }



    public function overallScrimKda(?int $teamId = null): float
    {
        $query = $this->scrimGamePlayers()
            ->whereHas('scrim', function ($q) use ($teamId) {
                $q->whereIn('status', [StatusScrim::COMPLETED, StatusScrim::ABORTED]);
                if ($teamId !== null) {
                    $q->where('team_id', $teamId);
                }
            });
        $totalKda = $query->sum('kills') + $query->sum('assists');
        $totalDeaths = $query->sum('deaths');
        if ($totalDeaths === 0) {
            return $totalKda;
        }
        return round($totalKda / $totalDeaths, 2);
    }

    public function favoriteChampionScrim(): ?string
    {
        return $this->scrimGamePlayers()
            ->select('champion')
            ->selectRaw('COUNT(*) as games_count')
            ->groupBy('champion')
            ->orderByDesc('games_count')
            ->value('champion');
    }
    
}
