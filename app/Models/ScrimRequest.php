<?php

namespace App\Models;

use App\Enums\StatusScrimRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScrimRequest extends Model
{
    protected $fillable = [
        'status',
        'scheduled_date',
        'scheduled_time',
        'number_of_games',
        'message',
        'responded_at',
        'requester_team_id',
        'receiver_team_id',
    ];

    protected $casts = [
        'status' => StatusScrimRequest::class,
        'scheduled_date' => 'date',
        'responded_at' => 'datetime',
    ];

    public function requesterTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'requester_team_id');
    }

    public function receiverTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'receiver_team_id');
    }

    public function scrims(): HasMany
    {
        return $this->hasMany(Scrim::class);
    }
}
