<?php

use App\Livewire\Forms\EditTaskForm;
use App\Models\Task;
use App\Models\TaskFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts::team')] class extends Component
{
    use WithFileUploads;

    public Task $task;

    public EditTaskForm $form;

    public string $newSubTask = '';

    public string $newLinkUrl = '';

    public string $newLinkTitle = '';

    public array $newFiles = [];

    public int $fileInputResetKey = 0;

    public function mount(int $id): void
    {
        $this->task = Task::query()
            ->with(['subtasks', 'links', 'files', 'teamMember.user'])
            ->whereKey($id)
            ->where('team_id', currentTeam()->id)
            ->firstOrFail();

        abort_unless(
            currentTeam()->creator_id === Auth::id() || currentMember()->isCoachOrStaff(),
            403,
        );

        $this->form->setTask($this->task);
    }

    #[Computed]
    public function assigneePlayerOption(): array
    {
        $member = $this->task->teamMember;

        return [
            [
                'id' => $member->id,
                'name' => $member->user?->username ?? '',
            ],
        ];
    }

    public function addSubtask(): void
    {
        if ($this->newSubTask === '') {
            session()->flash('error', __('pages/tasks/edit.error_empty_subtask'));

            return;
        }

        $this->form->subtasks[] = [
            'title' => $this->newSubTask,
            'is_completed' => false,
        ];
        $this->newSubTask = '';
    }

    public function removeSubtask(int $index): void
    {
        unset($this->form->subtasks[$index]);
        $this->form->subtasks = array_values($this->form->subtasks);
    }

    public function updatedNewFiles(): void
    {
        foreach ($this->newFiles as $file) {
            $this->form->files[] = $file;
        }
        $this->newFiles = [];
        $this->fileInputResetKey++;
    }

    public function removeFile(int $index): void
    {
        unset($this->form->files[$index]);
        $this->form->files = array_values($this->form->files);
    }

    public function removeExistingFile(int $taskFileId): void
    {
        $file = TaskFile::query()
            ->where('task_id', $this->task->id)
            ->whereKey($taskFileId)
            ->first();

        if ($file === null) {
            return;
        }

        Storage::disk('public')->delete($file->file_path);
        $file->delete();
        $this->task->load('files');
    }

    public function addLink(): void
    {
        $url = trim($this->newLinkUrl);

        if ($url === '') {
            session()->flash('link_error', __('pages/tasks/edit.error_empty_link'));

            return;
        }

        $this->form->links[] = [
            'url' => $url,
            'title' => trim($this->newLinkTitle) !== '' ? trim($this->newLinkTitle) : null,
        ];
        $this->newLinkUrl = '';
        $this->newLinkTitle = '';
    }

    public function removeLink(int $index): void
    {
        unset($this->form->links[$index]);
        $this->form->links = array_values($this->form->links);
    }

    public function update(): void
    {
        $this->form->update();
        session()->flash('toast', [
            'type' => 'success',
            'message' => __('toasts/toasts.task_updated'),
        ]);
        $this->redirect(route('tasks.index', ['slug' => currentTeam()->slug]));
    }
};

?>

<div>
    <section class="flex flex-col gap-8">
        <h2 class="text-2xl font-bold">
            {{ __('pages/tasks/edit.title') }}
        </h2>
        <form wire:submit.prevent="update" class="flex flex-col gap-6">
            <div class="flex flex-col gap-6">
                {{-- Informations principales --}}
                <fieldset class="flex flex-col gap-6 bg-bg-widget p-6 shadow-basic">
                    <legend class="sr-only">
                        {{ __('pages/tasks/edit.main_legend') }}
                    </legend>
                    <h3 class="text-2xl text-gold border-b border-gold pb-4 font-bold">
                        {{ __('pages/tasks/edit.main_legend') }}
                    </h3>
                    <div class="flex flex-col gap-4 md:flex-row md:gap-6">
                        <x-forms.input
                            :required="true"
                            wire:model.live="form.title"
                            class="w-full"
                            :label="__('pages/tasks/edit.field_title')"
                            :name="'title'"
                            :placeholder="__('pages/tasks/edit.field_title_placeholder')"
                            :type="'text'">
                            @error('form.title')
                            <p class="text-red-500 font-bold text-sm">{{ $message }}</p>
                            @enderror
                        </x-forms.input>
                        <x-forms.select
                            wire:model.live="form.assigneeUserId"
                            class="w-full"
                            :label="__('pages/tasks/edit.field_player')"
                            :name="'assignee_user_id'"
                            :options="$this->assigneePlayerOption"
                            :required="true"
                            :inputDisabled="true">
                            @error('form.assigneeUserId')
                            <p class="text-red-500 font-bold text-sm">{{ $message }}</p>
                            @enderror
                        </x-forms.select>
                        <x-forms.input
                            wire:model.live="form.dueDate"
                            class="w-full"
                            :label="__('pages/tasks/edit.field_due_date')"
                            :name="'due_date'"
                            :placeholder="''"
                            :required="true"
                            :type="'date'">
                            @error('form.dueDate')
                            <p class="text-red-500 font-bold text-sm">{{ $message }}</p>
                            @enderror
                        </x-forms.input>
                    </div>
                    <div class="flex flex-col gap-2">
                        <x-forms.textarea
                            wire:model.live="form.description"
                            :label="__('pages/tasks/edit.field_description')"
                            :name="'description'"
                            :placeholder="__('pages/tasks/edit.field_description_placeholder')">
                            @error('form.description')
                            <p class="text-red-500 font-bold text-sm">{{ $message }}</p>
                            @enderror
                        </x-forms.textarea>
                    </div>
                </fieldset>

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    {{-- Sous-tâches --}}
                    <fieldset x-data="{ addNewSubtasks: false }" class="flex flex-col gap-6 self-start bg-bg-widget p-6 shadow-basic">
                        <div class="flex  gap-4 items-center justify-between border-b border-gold pb-4">
                            <legend class="sr-only">
                                {{ __('pages/tasks/edit.subtasks_legend') }}
                            </legend>
                            <h3 class="text-2xl text-gold  font-bold">
                                {{ __('pages/tasks/edit.subtasks_legend') }}
                            </h3>
                            <button @click="addNewSubtasks = true" class="cta-primary shrink-0 cursor-pointer" type="button">
                                {{ __('pages/tasks/edit.add_subtask') }}
                            </button>
                        </div>

                        @if (count($this->form->subtasks) > 0)
                        <ul class="flex flex-col gap-2" role="list">
                            @foreach ($this->form->subtasks as $index => $subtask)
                            <li class="flex items-center bg-bg-card p-6 justify-between gap-2">
                                <span class="">{{ $subtask['title'] }}</span>
                                <button type="button" wire:click="removeSubtask({{ $index }})" class="cursor-pointer hover:text-red-700/90 transition-all duration-150">
                                    <flux:icon name="trash" class="size-5" />
                                </button>
                            </li>
                            @endforeach
                        </ul>
                        @else
                        <p class="text-text-secondary">
                            {{ __('pages/tasks/edit.no_subtasks') }}
                        </p>
                        @endif
                        @error('form.subtasks')
                        <p class="text-red-500 font-bold text-sm">{{ $message }}</p>
                        @enderror
                        <div x-show="addNewSubtasks" class="flex items-end justify-between gap-2">
                            <x-forms.input
                                :required="false"
                                :type="'text'"
                                wire:model="newSubTask"
                                :label="__('pages/tasks/edit.field_subtask_title')"
                                :name="'new_subtask_title'"
                                :placeholder="__('pages/tasks/edit.field_subtask_title_placeholder')">
                                @if (session('error'))
                                <p class="text-red-500 font-bold text-sm">{{ session('error') }}</p>
                                @endif
                            </x-forms.input>
                            <button type="button" x-on:click="addNewSubtasks = false" wire:click="addSubtask" class="cta-primary shrink-0 cursor-pointer" type="button">
                                {{ __('pages/tasks/edit.add_subtask') }}
                            </button>
                        </div>
                    </fieldset>

                    {{-- Ressources et fichiers --}}
                    <fieldset class="flex flex-col gap-6 bg-bg-widget self-start p-6 shadow-basic">
                        <legend class="sr-only">
                            {{ __('pages/tasks/edit.resources_legend') }}
                        </legend>
                        <h3 class="text-2xl text-gold border-b border-gold pb-4 font-bold">
                            {{ __('pages/tasks/edit.resources_legend') }}
                        </h3>

                        @if ($this->task->files->isNotEmpty())
                        <ul class="flex flex-col gap-2" role="list">
                            @foreach ($this->task->files as $storedFile)
                            <li class="flex items-center bg-bg-card p-6 justify-between gap-2">
                                <span class="flex items-center gap-3 min-w-0">
                                    <flux:icon name="document" class="size-5 shrink-0 text-gold" />
                                    <span class="truncate">{{ $storedFile->file_name }}</span>
                                </span>
                                <button type="button" wire:click="removeExistingFile({{ $storedFile->id }})" class="cursor-pointer hover:text-red-700/90 transition-all duration-150">
                                    <flux:icon name="trash" class="size-5" />
                                </button>
                            </li>
                            @endforeach
                        </ul>
                        @endif

                        @if (count($this->form->files) > 0)
                        <ul class="flex flex-col gap-2" role="list">
                            @foreach ($this->form->files as $index => $file)
                            <li class="flex items-center bg-bg-card p-6 justify-between gap-2">
                                <span class="flex items-center gap-3 min-w-0">
                                    <flux:icon name="document" class="size-5 shrink-0 text-gold" />
                                    <span class="truncate">{{ $file->getClientOriginalName() }}</span>
                                </span>
                                <button type="button" wire:click="removeFile({{ $index }})" class="cursor-pointer hover:text-red-700/90 transition-all duration-150">
                                    <flux:icon name="trash" class="size-5" />
                                </button>
                            </li>
                            @endforeach
                        </ul>
                        @endif

                        @if ($this->task->files->isEmpty() && count($this->form->files) === 0)
                        <p class="text-text-secondary">
                            {{ __('pages/tasks/edit.no_files') }}
                        </p>
                        @endif
                        @error('form.files.*')
                        <p class="text-red-500 font-bold text-sm">{{ $message }}</p>
                        @enderror

                        <div
                            class="border border-dashed transition-colors duration-150"
                            :class="dragging ? 'border-gold bg-input-bg/80' : 'border-input-border bg-input-bg'"
                            x-data="{ dragging: false }"
                            x-on:click="$refs.taskEditFilesInput.click()"
                            x-on:dragover.prevent="dragging = true"
                            x-on:dragleave="dragging = false"
                            x-on:drop.prevent="
                                dragging = false;
                                $refs.taskEditFilesInput.files = $event.dataTransfer.files;
                                $refs.taskEditFilesInput.dispatchEvent(new Event('change', { bubbles: true }));
                            ">
                            <label
                                for="task-files-input"
                                class="relative flex min-h-[8rem] flex-col items-center justify-center gap-3 px-4 py-8 text-center text-text-secondary cursor-pointer">
                                <flux:icon name="arrow-up-tray" class="size-10 text-text-secondary" />
                                <p class="text-sm md:text-base pointer-events-none">
                                    {{ __('pages/tasks/edit.upload_drag') }}
                                    <span class="font-semibold text-gold">{{ __('pages/tasks/edit.upload_browse') }}</span>
                                </p>
                                <input
                                    x-ref="taskEditFilesInput"
                                    type="file"
                                    id="task-files-input"
                                    wire:model="newFiles"
                                    wire:key="task-files-input-{{ $fileInputResetKey }}"
                                    multiple
                                    accept=".pdf,image/jpeg,image/png,image/webp"
                                    class="absolute inset-0 opacity-0 pointer-events-none" />
                            </label>
                        </div>
                        <div wire:loading wire:target="newFiles" class="text-sm text-text-secondary">
                            {{ __('pages/tasks/edit.upload_loading') }}
                        </div>
                    </fieldset>

                    {{-- Liens vidéo --}}
                    <fieldset x-data="{ addNewLink: false }" class="flex flex-col gap-6 self-start bg-bg-widget p-6 shadow-basic">
                        <div class="flex gap-4 items-center justify-between border-b border-gold pb-4">
                            <legend class="sr-only">
                                {{ __('pages/tasks/edit.links_legend') }}
                            </legend>
                            <h3 class="text-2xl text-gold font-bold">
                                {{ __('pages/tasks/edit.links_legend') }}
                            </h3>
                            <button @click="addNewLink = true" class="cta-primary shrink-0 cursor-pointer" type="button">
                                {{ __('pages/tasks/edit.add_link') }}
                            </button>
                        </div>

                        @if (count($this->form->links) > 0)
                        <ul class="flex flex-col gap-2" role="list">
                            @foreach ($this->form->links as $index => $link)
                            <li class="flex items-center bg-bg-card p-6 justify-between gap-2">
                                <span class="flex items-center gap-3 min-w-0">
                                    <flux:icon name="play" class="size-5 shrink-0 text-gold" />
                                    <span class="truncate">{{ $link['title'] ?? $link['url'] }}</span>
                                </span>
                                <button type="button" wire:click="removeLink({{ $index }})" class="cursor-pointer hover:text-red-700/90 transition-all duration-150">
                                    <flux:icon name="trash" class="size-5" />
                                </button>
                            </li>
                            @endforeach
                        </ul>
                        @else
                        <p class="text-text-secondary">
                            {{ __('pages/tasks/edit.no_links') }}
                        </p>
                        @endif
                        @error('form.links.*.url')
                        <p class="text-red-500 font-bold text-sm">{{ $message }}</p>
                        @enderror

                        <div x-show="addNewLink" class="flex flex-col gap-3">
                            <x-forms.input
                                :required="false"
                                :type="'url'"
                                wire:model="newLinkUrl"
                                :label="__('pages/tasks/edit.field_link_url')"
                                :name="'new_link_url'"
                                :placeholder="__('pages/tasks/edit.field_link_url_placeholder')">
                                @if (session('link_error'))
                                <p class="text-red-500 font-bold text-sm">{{ session('link_error') }}</p>
                                @endif
                            </x-forms.input>
                            <x-forms.input
                                :required="false"
                                :type="'text'"
                                wire:model="newLinkTitle"
                                :label="__('pages/tasks/edit.field_link_title')"
                                :name="'new_link_title'"
                                :placeholder="__('pages/tasks/edit.field_link_title_placeholder')" />
                            <div class="flex justify-end">
                                <button type="button" x-on:click="addNewLink = false" wire:click="addLink" class="cta-primary shrink-0 cursor-pointer">
                                    {{ __('pages/tasks/edit.add_link') }}
                                </button>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </div>
            <div class="flex flex-row justify-between gap-4 p-6 bg-bg-widget shadow-basic">
                <x-cta :href="route('tasks.index', ['slug' => currentTeam()->slug])" class="secondary" :title="__('pages/tasks/edit.cancel_title')">
                    {{ __('pages/tasks/edit.cancel_title') }}
                </x-cta>
                <x-forms.submit type="submit" variant="primary" :title="__('pages/tasks/edit.create_title')" class="w-fit" data-test="edit-task-button">
                    {{ __('pages/tasks/edit.create') }}
                </x-forms.submit>
            </div>

        </form>
    </section>
</div>