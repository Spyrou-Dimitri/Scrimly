<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subtask extends Model
{
    /**
     * Stored in migration as task_subtasks; default inflection would resolve to subtasks.
     */
    protected $table = 'task_subtasks';

    protected $fillable = [
        'title',
        'is_completed',
        'task_id',
    ];

    protected function casts(): array
    {
        return [
            'is_completed' => 'boolean',
        ];
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
}
