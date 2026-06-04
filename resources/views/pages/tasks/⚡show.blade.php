<?php

use App\Models\Subtask;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\On;
use App\Enums\StatusTask;
use App\Models\TaskSubmission;

new #[Layout('layouts::team')] class extends Component
{
    use WithFileUploads;

    public Task $task;

    public array $subtaskCompletion = [];

    public $submissions = [];

    public int $fileInputResetKey = 0;

    public string $newCommentContent = '';

    public function mount(int $id): void
    {
        $this->task = Task::query()
            ->with([
                'subtasks',
                'links',
                'files',
                'submissions',
                'teamMember.user',
                'createdBy.user',
            ])
            ->whereKey($id)
            ->where('team_id', currentTeam()->id)
            ->firstOrFail();


        $this->subtaskCompletion = $this->task->subtasks->pluck('is_completed', 'id')->toArray();
    }

    #[Computed]
    public function canManageTask(): bool
    {
        return Gate::allows('manageOnlyOurTasks', $this->task);
    }

    private function denyIfCannotManageTask(): bool
    {
        if (Gate::denies('manageOnlyOurTasks', $this->task)) {
            $this->dispatch('toast', [
                'title' => __('policies/task.error_title'),
                'message' => __('policies/task.error_interact_task'),
                'type' => 'error',
            ]);

            return true;
        }

        return false;
    }

    public function openCompleteTaskModal(): void
    {
        if ($this->denyIfCannotManageTask()) {
            return;
        }

        $this->dispatch('open_modal', [
            'form' => 'modals::tasks.complete-task',
            'model_id' => $this->task->id,
        ]);
    }

    public function openModalDeleteTask(): void
    {
        if (Gate::denies('manageTeam', User::class)) {
            $this->dispatch('toast', [
                'title' => __('policies/task.error_title'),
                'message' => __('policies/task.error_delete_task'),
                'type' => 'error',
            ]);

            return;
        }

        $this->dispatch('open_modal', [
            'form' => 'modals::tasks.delete-task',
            'model_id' => $this->task->id,
        ]);
    }

    #[Computed]
    public function isTaskCompleted(): bool
    {
        return $this->task->subtasks->every(fn(Subtask $subtask) => $subtask->is_completed);
    }

    public function saveSubtasks(): void
    {
        if ($this->denyIfCannotManageTask()) {
            return;
        }

        foreach ($this->subtaskCompletion as $subtaskId => $completed) {
            Subtask::query()
                ->where('task_id', $this->task->id)
                ->whereKey($subtaskId)
                ->update(['is_completed' => $completed]);
        }

        $this->refreshTasks();
        $this->dispatch('toast', [
            'type' => 'success',
            'message' => __('pages/tasks/show.subtasks_saved_toast'),
        ]);
        if ($this->isTaskCompleted && $this->task->status !== StatusTask::DONE) {
            $this->openCompleteTaskModal();
        } else if(!$this->isTaskCompleted && $this->task->status === StatusTask::DONE){
            $this->task->update(['status' => StatusTask::IN_PROGRESS]);
        } else if($this->task->status === StatusTask::TODO && $this->task->subtasks->contains('is_completed', true)){
            $this->task->update(['status' => StatusTask::IN_PROGRESS]);
        }
    }


    #[Computed]
    public function taskComments()
    {
        return TaskComment::query()
            ->with('teamMember.user')
            ->where('task_id', $this->task->id)
            ->orderBy('created_at', 'desc')
            ->paginate(6);
    }

    public function removeFile(int $index): void
    {
        if ($this->denyIfCannotManageTask()) {
            return;
        }

        unset($this->submissions[$index]);
    }

    public function uploadSubmissions(): void
    {
        if ($this->denyIfCannotManageTask()) {
            return;
        }

        foreach ($this->submissions as $submission) {
            TaskSubmission::create([
                'file_name' => $submission->getClientOriginalName(),
                'file_size' => $submission->getSize(),
                'file_path' => $submission->storeAs(config('taskSubmissions.path'), $submission->getClientOriginalName(), ['disk' => config('taskSubmissions.disk')]),
                'task_id' => $this->task->id,
            ]);
        }
        $this->submissions = [];
    }

    public function submitComment(): void
    {
        if ($this->denyIfCannotManageTask()) {
            return;
        }

        $this->validate([
            'newCommentContent' => ['required', 'string', 'max:2000'],
        ]);

        TaskComment::query()->create([
            'task_id' => $this->task->id,
            'team_member_id' => currentMember()->id,
            'content' => $this->newCommentContent,
        ]);

        $this->newCommentContent = '';

        $this->dispatch('toast', [
            'type' => 'success',
            'message' => __('pages/tasks/show.comments_saved_toast'),
        ]);
    }

    #[On('refresh_tasks')]
    public function refreshTasks(): void
    {
        $this->task->refresh();
        $this->task->load('subtasks');
    }
};
?>

<div class="flex flex-col gap-10">
    @php
    $canManageTeam = Gate::allows('manageTeam', User::class);
    @endphp

    <section class="flex flex-col gap-8">
        <div class="flex flex-col gap-6 lg:items-start lg:justify-between w-full">
            <div class="flex w-full flex-wrap items-center justify-between gap-3">
                <a
                    href="{{ route('tasks.index', ['slug' => currentTeam()->slug]) }}"
                    class="flex items-center gap-2 group transition-colors duration-150"
                    title="{{ __('pages/tasks/show.back_to_list') }}">
                    <flux:icon name="arrow-left" class="size-4 group-hover:text-gold transition-colors duration-150" />
                    <span class="group-hover:text-gold transition-colors duration-150">{{ __('pages/tasks/show.back_to_list') }}</span>
                </a>
                @if ($canManageTeam)
                <div class="flex flex-wrap items-center gap-2">
                    <x-cta
                        :href="route('tasks.edit', ['slug' => currentTeam()->slug, 'id' => $this->task->id])"
                        :class="'primary'"
                        :title="__('pages/tasks/show.action_edit_task')">
                        {{ __('pages/tasks/show.action_edit_task') }}
                    </x-cta>
                    <x-destructive :type="'button'" wire:click="openModalDeleteTask" :title="__('pages/tasks/show.action_delete_task')">
                        <flux:icon name="trash" class="size-5 text-white" />
                        {{ __('pages/tasks/show.action_delete_task') }}
                    </x-destructive>
                </div>
                @endif
            </div>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center w-full sm:justify-between">
                <div class="flex flex-row gap-4 flex-wrap">
                    <h2 class="text-[32px] font-bold text-white break-words">
                        {{ $this->task->title }}
                    </h2>
                    <span class="flex items-center {{ $this->task->status->macaron() }} ">
                        {{ $this->task->status->label() }}
                    </span>
                </div>
                @if ($this->canManageTask && $this->isTaskCompleted && $this->task->status !== StatusTask::DONE)
                <button type="button"  wire:click="openCompleteTaskModal" class="cta-secondary group inline-flex shrink-0 flex-row items-center gap-2">
                    <flux:icon name="check" class="size-6 text-gold group-hover:text-black transition-colors duration-150" />
                    {{ __('pages/tasks/show.action_complete_task') }}
                </button>
                @endif
            </div>
        </div>


        <div class="grid grid-cols-1 gap-6 lg:grid-cols-12 lg:gap-8">
            <div class="lg:col-span-8 flex flex-col gap-6">

                {{-- Description --}}
                <article class="bg-bg-widget p-6 shadow-basic flex flex-col gap-4">
                    <h3 class="text-2xl text-gold font-bold">
                        {{ __('pages/tasks/show.section_description') }}
                    </h3>
                    @if (filled($this->task->description))
                    <div class="prose prose-invert max-w-none text-text-primary">
                        {!! nl2br(e($this->task->description)) !!}
                    </div>
                    @else
                    <p class="text-text-secondary">
                        {{ __('pages/tasks/show.description_empty') }}
                    </p>
                    @endif
                </article>

                {{-- Fichiers --}}
                <article class="bg-bg-widget p-6 shadow-basic flex flex-col gap-4">
                    <h3 class="text-2xl text-gold font-bold">
                        {{ __('pages/tasks/show.section_files') }}
                    </h3>
                    @if ($this->task->files->isNotEmpty())
                    <ul class="flex flex-col gap-2" role="list">
                        @foreach ($this->task->files as $file)
                        @php($ext = strtolower(pathinfo($file->file_name, PATHINFO_EXTENSION)))
                        <li class="flex items-center bg-bg-card px-6 py-4 gap-3 min-w-0">
                            @if (in_array($ext, ['mp4', 'webm', 'mov'], true))
                            <flux:icon name="play" class="size-6 shrink-0 text-gold" />
                            @else
                            <flux:icon name="document" class="size-6 shrink-0 text-gold" />
                            @endif
                            <a
                                href="{{ Storage::disk(config('taskFiles.disk'))->url($file->file_path) }}"
                                class="truncate hover:text-gold transition-colors duration-150"
                                target="_blank"
                                rel="noopener noreferrer">
                                {{ $file->file_name }}
                            </a>
                        </li>
                        @endforeach
                    </ul>
                    @else
                    <p class="text-text-secondary">
                        {{ __('pages/tasks/show.files_empty') }}
                    </p>
                    @endif
                </article>

                {{-- Liens --}}
                <article class="bg-bg-widget p-6 shadow-basic flex flex-col gap-4">
                    <h3 class="text-2xl text-gold font-bold">
                        {{ __('pages/tasks/show.section_links') }}
                    </h3>
                    @if ($this->task->links->isNotEmpty())
                    <ul class="flex flex-col gap-2" role="list">
                        @foreach ($this->task->links as $link)
                        <li class="flex items-center bg-bg-card px-6 py-4 gap-3 min-w-0">
                            <flux:icon name="play" class="size-6 shrink-0 text-gold" />
                            <a
                                href="{{ $link->url }}"
                                class="truncate hover:text-gold transition-colors duration-150"
                                target="_blank"
                                rel="noopener noreferrer">
                                {{ $link->title ?? $link->url }}
                            </a>
                        </li>
                        @endforeach
                    </ul>
                    @else
                    <p class="text-text-secondary">
                        {{ __('pages/tasks/show.links_empty') }}
                    </p>
                    @endif
                </article>

                {{-- Méta --}}
                <article class="bg-bg-widget p-6 shadow-basic flex flex-col gap-6">
                    <h3 class="text-2xl text-gold font-bold">
                        {{ __('pages/tasks/show.section_meta') }}
                    </h3>
                    <dl class="flex flex-col gap-3 text-white">
                        <div class="flex flex-col gap-1 sm:flex-row">
                            <dt class="text-text-secondary">
                                {{ __('pages/tasks/show.meta_author') }} :
                            </dt>
                            <dd class="font-medium">
                                {{ $this->task->createdBy->user->username }}
                            </dd>
                        </div>
                        <div class="flex flex-col gap-1 sm:flex-row">
                            <dt class="text-text-secondary">
                                {{ __('pages/tasks/show.meta_created_at') }} :
                            </dt>
                            <dd class="font-medium">
                                {{ $this->task->created_at->translatedFormat('d/m/Y') }}
                            </dd>
                        </div>
                        <div class="flex flex-col gap-1 sm:flex-row">
                            <dt class="text-text-secondary">
                                {{ __('pages/tasks/show.meta_assignee') }} :
                            </dt>
                            <dd class="font-medium">
                                {{ $this->task->teamMember->user->username }}
                            </dd>
                        </div>
                        <div class="flex flex-col gap-1 sm:flex-row">
                            <dt class="text-text-secondary">
                                {{ __('pages/tasks/show.meta_deadline') }} :
                            </dt>
                            <dd class="font-medium">
                                @if ($this->task->deadline)
                                {{ $this->task->deadline->translatedFormat('d/m/Y') }}
                                @else
                                {{ __('pages/tasks/show.meta_deadline_empty') }}
                                @endif
                            </dd>
                        </div>
                    </dl>
                </article>
            </div>

            <div class="lg:col-span-4 flex flex-col gap-6">

                {{-- Sous-tâches --}}
                <article class="bg-bg-widget p-6 shadow-basic flex flex-col gap-6">
                    <h3 class="text-2xl text-gold font-bold flex items-center justify-between gap-2">
                        {{ __('pages/tasks/show.section_subtasks') }}
                        <span class="text-text-secondary font-normal text-xl">
                            {{ collect($subtaskCompletion)->filter()->count() }} / {{ count($subtaskCompletion) }}
                        </span>
                    </h3>

                    @if ($this->task->subtasks->isNotEmpty())
                    <ul class="flex flex-col gap-3" role="list">
                        @foreach ($this->task->subtasks as $subtask)
                        <li class="flex items-center gap-3 bg-bg-card px-4 py-3">
                            @if ($this->canManageTask)
                            <input
                                type="checkbox"
                                wire:model="subtaskCompletion.{{ $subtask->id }}"
                                id="subtask-{{ $subtask->id }}"
                                class="size-4 shrink-0 border-input-border checked:bg-gold text-gold focus:ring-gold" />
                            <label for="subtask-{{ $subtask->id }}" class="cursor-pointer flex-1">
                                {{ $subtask->title }}
                            </label>
                            @else
                            <span
                                class="flex size-4 shrink-0 items-center justify-center border border-input-border text-xs {{ $subtask->is_completed ? 'bg-gold text-black' : 'text-transparent' }}"
                                aria-hidden="true">&#10003;</span>
                            <span class="flex-1 {{ $subtask->is_completed ? 'line-through text-text-secondary' : '' }}">
                                {{ $subtask->title }}
                            </span>
                            @endif
                        </li>
                        @endforeach
                    </ul>
                    @if ($this->canManageTask)
                    <div class="flex justify-center">
                        <button type="button" wire:click="saveSubtasks" class="cta-primary w-full cursor-pointer">
                            {{ __('pages/tasks/show.save_subtasks') }}
                        </button>
                    </div>
                    @endif
                    @else
                    <p class="text-text-secondary">
                        {{ __('pages/tasks/show.subtasks_empty') }}
                    </p>
                    @endif
                </article>

                {{-- Upload --}}
                <article class="bg-bg-widget p-6 shadow-basic flex flex-col gap-6">
                    <h3 class="text-2xl text-gold font-bold">
                        {{ __('pages/tasks/show.section_upload') }}
                    </h3>

                    {{-- Fichiers déjà stockés côté serveur --}}
                    <div class="flex flex-col gap-3">
                        <h4 class="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-semibold uppercase tracking-wide text-text-secondary">
                            <span>{{ __('pages/tasks/show.upload_existing_heading') }}</span>
                            @if ($this->task->submissions->isNotEmpty())
                            <span class="inline-flex items-center justify-center min-h-6 min-w-6 bg-gold/15 px-2 text-gold text-xs font-bold tabular-nums">
                                {{ $this->task->submissions->count() }}
                            </span>
                            @endif
                        </h4>
                        @if ($this->task->submissions->isNotEmpty())
                        <ul class="flex flex-col gap-2" role="list" aria-label="{{ __('pages/tasks/show.upload_existing_heading') }}">
                            @foreach ($this->task->submissions as $submission)
                            <li class="flex items-center justify-between gap-2 border border-input-border bg-bg-card px-4 py-4">
                                <a target="_blank" rel="noopener noreferrer" href="{{ Storage::disk(config('taskSubmissions.disk'))->url($submission->file_path) }}" class="flex items-center gap-3 min-w-0">
                                    <flux:icon name="document" class="size-5 shrink-0 text-gold" />
                                    <span class="truncate hover:text-gold transition-colors duration-150">{{ $submission->file_name }}</span>
                                </a>
                            </li>
                            @endforeach
                        </ul>
                        @else
                        <p class="border border-dashed border-input-border bg-bg-card/40 px-4 py-6 text-sm text-text-secondary text-center">
                            {{ __('pages/tasks/show.upload_existing_empty') }}
                        </p>
                        @endif
                    </div>


                    {{-- Fichiers submissions --}}
                    @if ($this->canManageTask)
                    <form wire:submit.prevent="uploadSubmissions" class="flex flex-col gap-4">
                        <div class="flex flex-col gap-3">
                            <h4 class="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-semibold uppercase tracking-wide text-gold">
                                <span>{{ __('pages/tasks/show.upload_pending_heading') }}</span>
                                @if (count($this->submissions) > 0)
                                <span class="inline-flex items-center justify-center min-h-6 min-w-6  border border-gold/40 bg-gold/10 px-2 text-gold text-xs font-bold tabular-nums">
                                    {{ count($this->submissions) }}
                                </span>
                                @endif
                            </h4>
                            @if (count($this->submissions) > 0)
                            <ul class="flex flex-col gap-2" role="list" aria-label="{{ __('pages/tasks/show.upload_pending_heading') }}">
                                @foreach ($this->submissions as $index => $submission)
                                <li class="flex items-center justify-between gap-2 border border-dashed border-gold/35 bg-input-bg/60 px-4 py-4">
                                    <a target="_blank" rel="noopener noreferrer" href="{{ $submission->temporaryUrl() }}" class="flex items-center gap-3 min-w-0">
                                        <flux:icon name="document" class="size-5 shrink-0 text-gold" />
                                        <span class="truncate hover:text-gold transition-colors duration-150">{{ $submission->getClientOriginalName() }}</span>
                                    </a>
                                    <button type="button" wire:click="removeFile({{ $index }})" class="cursor-pointer hover:text-red-700/90 transition-all duration-150">
                                        <flux:icon name="trash" class="size-5" />
                                    </button>
                                </li>
                                @endforeach
                            </ul>
                            @else
                            <p class="text-text-secondary text-sm">
                                {{ __('pages/tasks/create.no_files') }}
                            </p>
                            @endif
                            @error('submissions')
                            <p class="text-red-500 font-bold text-sm">{{ $message }}</p>
                            @enderror
                        </div>
                        <div
                            class="border border-dashed border-input-border transition-colors hover:border-gold bg-input-bg duration-150"
                            :class="{ 'border-gold': dragging }"
                            x-data="{ dragging: false }"
                            x-on:click="$refs.taskShowFileInput.click()"
                            x-on:dragover.prevent="dragging = true"
                            x-on:dragleave="dragging = false"
                            x-on:drop.prevent="
                                dragging = false;
                                $refs.taskShowFileInput.files = $event.dataTransfer.files;
                                $refs.taskShowFileInput.dispatchEvent(new Event('change', { bubbles: true }));
                            ">
                            <label
                                for="task-show-upload"
                                class="relative flex min-h-[10rem] flex-col items-center justify-center gap-3 px-4 py-10 text-center text-text-secondary cursor-pointer">
                                <flux:icon name="arrow-up-tray" class="size-10 text-text-secondary" />
                                <p class="text-sm md:text-base pointer-events-none">
                                    {{ __('pages/tasks/show.upload_drag') }}
                                    <span class="font-semibold text-gold">{{ __('pages/tasks/show.upload_browse') }}</span>
                                </p>
                                <input
                                    x-ref="taskShowFileInput"
                                    type="file"
                                    id="task-show-upload"
                                    wire:model="submissions"
                                    multiple
                                    accept=".pdf,image/jpeg,image/png,image/webp"
                                    class="absolute inset-0 opacity-0 pointer-events-none" />
                            </label>
                        </div>

                        <x-forms.submit>
                            {{ __('pages/tasks/show.upload_submit') }}
                        </x-forms.submit>
                    </form>
                    <div wire:loading wire:target="submissions" class="text-sm text-text-secondary">
                        {{ __('pages/tasks/edit.upload_loading') }}
                    </div>
                    @error('submissions.*')
                    <p class="text-red-500 font-bold text-sm">{{ $message }}</p>
                    @enderror
                    @endif
                </article>
            </div>
        </div>


    </section>

    {{-- Commentaires --}}
    <section class="flex flex-col gap-6">
        <h2 class="text-[32px] font-bold">
            {{ __('pages/tasks/show.comments_count') }} ({{ $this->taskComments->count() }})
        </h2>

        @if ($this->taskComments->isEmpty())
        <p class="text-text-secondary">{{ __('pages/tasks/show.comments_empty') }}</p>
        @else
        <ul class="flex flex-col gap-4" role="list">
            @foreach ($this->taskComments as $comment)
            <li class="bg-bg-widget p-6 shadow-basic flex flex-col gap-3">
                <div class="flex flex-row flex-wrap items-baseline justify-between gap-x-4 gap-y-1">
                    <p class="text-gold font-semibold text-sm sm:text-base">
                        {{ $comment->teamMember->roleInTeam->label() }}
                        @if ($comment->teamMember->user)
                        - {{ $comment->teamMember->user->username }}
                        @endif
                    </p>
                    <time
                        datetime="{{ $comment->created_at->toIso8601String() }}"
                        class="text-white text-sm shrink-0 ml-auto">
                        {{ $comment->created_at->translatedFormat('l H:i') }}
                    </time>
                </div>
                <div class="text-white text-sm">
                    {{ $comment->content }}
                </div>
            </li>
            @endforeach
        </ul>
        {{ $this->taskComments->links() }}
        @endif

        @if ($this->canManageTask)
        <form wire:submit.prevent="submitComment" class="flex flex-col bg-bg-widget p-6 shadow-basic sm:flex-row sm:items-stretch gap-3 sm:gap-4">
            <div class="flex flex-1 min-w-0">
                <x-forms.input
                    wire:model="newCommentContent"
                    :type="'text'"
                    :srOnlyLabel="true"
                    :name="'task-show-comment-body'"
                    :label="__('pages/tasks/show.comment_placeholder')"
                    :placeholder="__('pages/tasks/show.comment_placeholder')">
                    @error('newCommentContent')
                    <p class="text-red-500 font-bold text-sm">{{ $message }}</p>
                    @enderror
                </x-forms.input>
            </div>
            <x-forms.submit>
                {{ __('pages/tasks/show.comment_send') }}
            </x-forms.submit>
        </form>
        @endif
    </section>
</div>