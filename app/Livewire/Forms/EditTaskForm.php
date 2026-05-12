<?php

namespace App\Livewire\Forms;

use App\Models\Subtask;
use App\Models\Task;
use App\Models\TaskFile;
use App\Models\TaskLink;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Form;

class EditTaskForm extends Form
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
    public array $links = [];

    #[Validate]
    public array $files = [];

    public ?Task $task = null;

    public function setTask(Task $task): void
    {
        $this->task = $task;

        $this->title = $task->title;
        $this->description = $task->description ?? '';
        $this->dueDate = $task->deadline->format('Y-m-d');
        $this->assigneeUserId = $task->team_member_id;

        $this->subtasks = $task->subtasks
            ->map->only(['id', 'title', 'is_completed'])
            ->toArray();
        $this->links = $task->links
            ->map->only(['id', 'url', 'title'])
            ->toArray();

        $this->files = [];
    }

    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150', 'min:3'],
            'description' => ['nullable', 'string', 'max:1000'],
            'dueDate' => ['required', 'date'],
            'assigneeUserId' => ['required', Rule::in([(string) $this->task->team_member_id])],
            'subtasks' => ['required', 'array', 'min:1'],
            'subtasks.*.id' => [
                'nullable',
                'integer',
                Rule::exists('task_subtasks', 'id')->where('task_id', $this->task->id),
            ],
            'subtasks.*.title' => ['required', 'string', 'max:150', 'min:3'],
            'subtasks.*.is_completed' => ['required', 'boolean'],
            'files' => ['array'],
            'files.*' => ['file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,webp'],
            'links' => ['array'],
            'links.*.id' => [
                'nullable',
                'integer',
                Rule::exists('task_links', 'id')->where('task_id', $this->task->id),
            ],
            'links.*.url' => ['required', 'url', 'max:2048'],
            'links.*.title' => ['nullable', 'string', 'max:150'],
        ];
    }

    public function update(): void
    {
        $this->validate();

        DB::transaction(function (): void {
            $this->task->update([
                'title' => $this->title,
                'description' => $this->description !== '' ? $this->description : null,
                'deadline' => $this->dueDate,
            ]);

            $keptSubtaskIds = collect($this->subtasks)
                ->pluck('id')
                ->map(fn (mixed $id): int => (int) $id)
                ->all();

            Subtask::query()
                ->where('task_id', $this->task->id)
                ->whereNotIn('id', $keptSubtaskIds)
                ->delete();

            foreach ($this->subtasks as $row) {
                if (! empty($row['id'])) {
                    Subtask::query()
                        ->where('task_id', $this->task->id)
                        ->whereKey((int) $row['id'])
                        ->update([
                            'title' => $row['title'],
                            'is_completed' => (bool) $row['is_completed'],
                        ]);
                } else {
                    Subtask::create([
                        'task_id' => $this->task->id,
                        'title' => $row['title'],
                        'is_completed' => (bool) $row['is_completed'],
                    ]);
                }
            }

            $keptLinkIds = collect($this->links)->pluck('id')->filter()->map(fn (mixed $id): int => (int) $id)->values()->all();

            TaskLink::query()
                ->where('task_id', $this->task->id)
                ->whereNotIn('id', $keptLinkIds)
                ->delete();

            foreach ($this->links as $row) {
                if (! empty($row['id'])) {
                    TaskLink::query()
                        ->where('task_id', $this->task->id)
                        ->whereKey((int) $row['id'])
                        ->update([
                            'url' => $row['url'],
                            'title' => ! empty($row['title']) ? $row['title'] : null,
                        ]);
                } else {
                    TaskLink::create([
                        'task_id' => $this->task->id,
                        'url' => $row['url'],
                        'title' => ! empty($row['title']) ? $row['title'] : null,
                    ]);
                }
            }
        });

        foreach ($this->files as $temporaryFile) {
            $extension = $temporaryFile->extension() ?: $temporaryFile->getClientOriginalExtension();
            $newName = uniqid().'.'.$extension;

            $fullPath = Storage::disk('public')->putFileAs(
                config('taskFiles.original_path').'/'.$this->task->id,
                $temporaryFile,
                $newName,
            );

            if ($fullPath === false || $fullPath === null) {
                continue;
            }

            TaskFile::create([
                'task_id' => $this->task->id,
                'uploaded_by' => Auth::id(),
                'file_name' => $temporaryFile->getClientOriginalName(),
                'file_path' => $fullPath,
                'file_size' => $temporaryFile->getSize(),
            ]);
        }

        $this->files = [];
    }
}
