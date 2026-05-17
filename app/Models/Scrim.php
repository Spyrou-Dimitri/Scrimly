<?php

namespace App\Models;

use App\Enums\StatusScrim;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Scrim extends Model
{
    protected $fillable = [
        'scheduled_date',
        'scheduled_time',
        'number_of_games',
        'status',
        'notes',
        'advantages',
        'disadvantages',
        'scrim_request_id',
        'opponent_team_id',
    ];
    protected $casts = [
        'scheduled_date' => 'date',
        'number_of_games' => 'integer',
        'status' => StatusScrim::class,
    ];
    public function scrimRequest(): BelongsTo
    {
        return $this->belongsTo(ScrimRequest::class);
    }
    
}
