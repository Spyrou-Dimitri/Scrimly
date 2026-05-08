<?php

namespace App\Livewire\Forms;

use App\Models\Subtask;
use App\Models\Task;
use App\Models\TeamMember;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Form;

class CreateTaskForm extends Form
{
    #[Validate]
    public string $title = '';

    #[Validate]
    public string $description = '';

    #[Validate]
    public string $dueDate = '';

    #[Validate]
    public string $assigneeUserId = '';

    #[Validate]
    public array $subtasks = [];

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150', 'min:3'],
            'description' => ['nullable', 'string', 'max:1000'],
            'dueDate' => ['required', 'date', 'after_or_equal:today'],
            'assigneeUserId' => ['required', 'exists:team_members,id'],
            'subtasks' => ['required', 'array', 'min:1'],
            'subtasks.*.title' => ['required', 'string', 'max:150', 'min:3'],
            'subtasks.*.is_completed' => ['required', 'boolean'],
        ];
    }

    public function store(): void
    {
        $this->validate();

        $team = currentTeam();

        abort_if(! $team, 403);

        $creatorMembership = TeamMember::query()
            ->where('team_id', $team->id)
            ->where('user_id', Auth::id())
            ->first();

        abort_if($creatorMembership === null, 403);

        $task = Task::create([
            'title' => $this->title,
            'description' => $this->description !== '' ? $this->description : null,
            'deadline' => $this->dueDate,
            'created_by' => $creatorMembership->id,
            'team_member_id' => $this->assigneeUserId,
        ]);
        foreach ($this->subtasks as $subtask) {
            Subtask::create([
                'title' => $subtask['title'],
                'is_completed' => false,
                'task_id' => $task->id,
            ]);
        }
    }
}
