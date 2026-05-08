<?php

namespace App\Models;

use App\Enums\StatusTask;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    protected $fillable = [
        'title',
        'description',
        'status',
        'deadline',
        'created_by',
        'team_member_id',
        'team_id',
        'completed_at',
    ];

    protected $casts = [
        'status' => StatusTask::class,
        'deadline' => 'date',
        'completed_at' => 'datetime',
    ];


    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(TeamMember::class, 'created_by');
    }

    public function teamMember(): BelongsTo
    {
        return $this->belongsTo(TeamMember::class, 'team_member_id');
    }

    public function subtasks(): HasMany
    {
        return $this->hasMany(Subtask::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(TaskSubmission::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(TaskFile::class);
    }

    public function links(): HasMany
    {
        return $this->hasMany(TaskLink::class);
    }
}
