<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerDefaultSchedule extends Model
{
    protected $fillable = [
        'team_member_id',
        'day_of_week',
        'start_time',
        'end_time',
    ];

    public function teamMember(): BelongsTo
    {
        return $this->belongsTo(TeamMember::class);
    }

    public function getStartTimeAttribute($value): string
    {
        return Carbon::parse($value)->format('H:i');
    }

    public function getEndTimeAttribute($value): string
    {
        return Carbon::parse($value)->format('H:i');
    }
}
