<?php

namespace App\Models;

use App\Enums\AbsenceJustification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Absence extends Model
{
    protected $fillable = ['date', 'justification', 'team_member_id'];

    protected $casts = [
        'date' => 'date',
        'justification' => AbsenceJustification::class,
    ];

    public function teamMember(): BelongsTo
    {
        return $this->belongsTo(TeamMember::class);
    }

    public function getDateFormattedAttribute(): string
    {
        return $this->date->translatedFormat('d F (l)');
    }
}
