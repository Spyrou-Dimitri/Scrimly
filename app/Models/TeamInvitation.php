<?php

namespace App\Models;

use App\Enums\RoleInGame;
use App\Enums\RoleInTeam;
use App\Enums\StatusInvitation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamInvitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'status',
        'roleInTeam',
        'roleInGame',
        'motivation',
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
