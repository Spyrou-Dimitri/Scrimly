<?php

namespace App\Livewire\Forms;

use App\Models\Subtask;
use App\Models\Task;
use App\Models\TaskFile;
use App\Models\TaskLink;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
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

    #[Validate]
    public array $files = [];

    #[Validate]
    public array $links = [];

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
            'files' => ['array'],
            'files.*' => ['file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,webp'],
            'links' => ['array'],
            'links.*.url' => ['required', 'url', 'max:2048'],
            'links.*.title' => ['nullable', 'string', 'max:150'],
        ];
    }

    public function store(): bool
    {
        if (Gate::denies('manageTeam', User::class)) {
            return false;
        }

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
            'team_id' => $team->id,
        ]);

        foreach ($this->subtasks as $subtask) {
            Subtask::create([
                'title' => $subtask['title'],
                'is_completed' => false,
                'task_id' => $task->id,
            ]);
        }

        foreach ($this->files as $temporaryFile) {
            $extension = $temporaryFile->extension() ?: $temporaryFile->getClientOriginalExtension();
            $newName = uniqid().'.'.$extension;

            $fullPath = Storage::disk('public')->putFileAs(
                config('taskFiles.original_path').'/'.$task->id,
                $temporaryFile,
                $newName,
            );

            if ($fullPath === false || $fullPath === null) {
                continue;
            }

            TaskFile::create([
                'task_id' => $task->id,
                'uploaded_by' => Auth::id(),
                'file_name' => $temporaryFile->getClientOriginalName(),
                'file_path' => $fullPath,
                'file_size' => $temporaryFile->getSize(),
            ]);
        }

        foreach ($this->links as $link) {
            TaskLink::create([
                'task_id' => $task->id,
                'url' => $link['url'],
                'title' => ! empty($link['title']) ? $link['title'] : null,
            ]);
        }

        $this->files = [];

        return true;
    }
}
