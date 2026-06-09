<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\TypeEvents;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;
class Event extends Model
{
    protected $fillable = [
        'title',
        'date',
        'start_time',
        'end_time',
        'all_day',
        'type',
        'team_id',
    ];

    protected $casts = [
        'date' => 'date',
        'all_day' => 'boolean',
        'type' => TypeEvents::class,
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
    public function scheduledStart(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => Carbon::parse($this->date->format('Y-m-d').' '.$this->start_time),
        );
    }
    public function scheduledEnd(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => Carbon::parse($this->date->format('Y-m-d').' '.$this->end_time),
        );
    }
}
