<?php

namespace App\Models;

use App\Enums\StatusScrim;
use App\Enums\ScrimOutcome;
use App\Observers\ScrimObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

#[ObservedBy(ScrimObserver::class)]
class Scrim extends Model
{
    protected $fillable = [
        'scheduled_date',
        'scheduled_time',
        'number_of_games',
        'status',
        'summary',
        'advantages',
        'outcome',
        'disadvantages',
        'scrim_request_id',
        'opponent_team_id',
        'team_id',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'number_of_games' => 'integer',
        'status' => StatusScrim::class,
        'outcome' => ScrimOutcome::class,
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
        return $this->hasMany(ScrimGame::class)->orderBy('id');
    }

    protected function scheduledAt(): Attribute
    {
        return Attribute::get(function (): Carbon {
            return Carbon::parse(
                $this->scheduled_date->format('Y-m-d').' '.$this->scheduled_time
            );
        });
    }
}
