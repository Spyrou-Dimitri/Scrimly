<?php

use App\Enums\RoleInTeam;
use App\Enums\StatusInTeam;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use App\Livewire\Forms\CreateTaskForm;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts::team')] class extends Component
{
    use WithFileUploads;

    public CreateTaskForm $form;
    public string $newSubTask = '';
    public string $newLinkUrl = '';
    public string $newLinkTitle = '';
    public array $newFiles = [];
    public int $fileInputResetKey = 0;

    public function mount(): void
    {
        abort_unless(Gate::allows('manageTeam', User::class), 403);
    }

    #[Computed]
    public function assignablePlayers(): array
    {
        $team = currentTeam();

        if (! $team) {
            return [];
        }

        return TeamMember::query()
            ->where('team_id', $team->id)
            ->where('roleInTeam', RoleInTeam::PLAYER)
            ->where('status', StatusInTeam::ACCEPTED)
            ->with('user')
            ->orderBy('joined_at', 'desc')
            ->get()
            ->map(fn(TeamMember $member): array => [
                'id' => $member->id,
                'name' => $member->user?->username ?? '',
            ])
            ->all();
    }

    public function addSubtask(): void
    {
        if (empty($this->newSubTask) || mb_strlen($this->newSubTask) < 3) {
            session()->flash('errorNewSubtask', __('pages/tasks/create.error_too_short_subtask'));
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
        try {
            $this->validate(
                [
                    'newFiles.*' => ['file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,webp'],
                ],
                [
                    'newFiles.*.uploaded' => __('pages/tasks/create.error_file_upload'),
                    'newFiles.*.max' => __('pages/tasks/create.error_file_too_large'),
                    'newFiles.*.mimes' => __('pages/tasks/create.error_file_type'),
                ],
                [
                    'newFiles.*' => __('pages/tasks/create.field_file'),
                ]
            );
        } catch (ValidationException $exception) {
            $this->addError('newFiles', $exception->validator->errors()->first());

            $this->newFiles = [];
            $this->fileInputResetKey++;

            return;
        }

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

    public function addLink(): void
    {
        $url = trim($this->newLinkUrl);

        if ($url === '') {
            session()->flash('errorNewLink', __('pages/tasks/create.error_empty_link'));
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


    public function store(): void
    {
        if (! $this->form->store()) {
            $this->dispatch('toast', [
                'title' => __('policies/task.error_title'),
                'message' => __('policies/task.error_create_task'),
                'type' => 'error',
            ]);
            return;
        }
        session()->flash('toast', [
            'type' => 'success',
            'message' => __('toasts/toasts.task_created'),
        ]);
        $this->redirect(route('tasks.index', ['slug' => currentTeam()->slug]));
    }
};

?>

<div>
    <section class="flex flex-col gap-8" aria-labelledby="tasks-create-heading">
        <h2 id="tasks-create-heading" class="text-[32px] font-bold">
            {{ __('pages/tasks/create.title') }}
        </h2>
        <form wire:submit.prevent="store" class="flex flex-col gap-6">
            <div class="flex flex-col gap-6">
                {{-- Informations principales --}}
                <fieldset class="flex flex-col gap-6 bg-bg-widget p-6 shadow-basic">
                    <legend class="sr-only">
                        {{ __('pages/tasks/create.main_legend') }}
                    </legend>
                    <h3 class="text-2xl text-gold border-b border-gold pb-4 font-bold">
                        {{ __('pages/tasks/create.main_legend') }}
                    </h3>
                    <div class="flex flex-col gap-4 md:flex-row md:gap-6">
                        <x-forms.input
                            :required="true"
                            wire:model.live="form.title"
                            class="w-full"
                            :label="__('pages/tasks/create.field_title')"
                            :name="'title'"
                            :placeholder="__('pages/tasks/create.field_title_placeholder')"
                            :placeholder="__('pages/tasks/create.field_title_placeholder')"
                            :type="'text'">
                            @error('form.title')
                            <p class="text-red-500 font-bold text-sm">{{ $message }}</p>
                            @enderror
                        </x-forms.input>
                        <x-forms.select
                            wire:model.live="form.assigneeUserId"
                            class="w-full"
                            :disabled="__('pages/tasks/create.field_player_placeholder')"
                            :label="__('pages/tasks/create.field_player')"
                            :name="'assignee_user_id'"
                            :options="$this->assignablePlayers"
                            :required="true">
                            @error('form.assigneeUserId')
                            <p class="text-red-500 font-bold text-sm">{{ $message }}</p>
                            @enderror
                        </x-forms.select>
                        <x-forms.input
                            wire:model.live="form.dueDate"
                            class="w-full"
                            :label="__('pages/tasks/create.field_due_date')"
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
                            :label="__('pages/tasks/create.field_description')"
                            :name="'description'"
                            :placeholder="__('pages/tasks/create.field_description_placeholder')">
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
                                {{ __('pages/tasks/create.subtasks_legend') }}
                            </legend>
                            <h3 class="text-2xl text-gold  font-bold">
                                {{ __('pages/tasks/create.subtasks_legend') }}
                            </h3>
                            <button @click="addNewSubtasks = true" class="cta-primary shrink-0 cursor-pointer" type="button">
                                {{ __('pages/tasks/create.add_subtask') }}
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
                            {{ __('pages/tasks/create.no_subtasks') }}
                        </p>
                        @endif
                        @error('form.subtasks')
                        <p class="text-red-500 font-bold text-sm">{{ $message }}</p>
                        @enderror
                        <div x-show="addNewSubtasks" x-cloak class="flex flex-col gap-2">
                            <div class="flex items-center gap-2">
                                <x-forms.input
                                    :required="false"
                                    @keydown.enter.prevent="$wire.addSubtask()"
                                    :type="'text'"
                                    wire:model="newSubTask"
                                    :label="__('pages/tasks/create.field_subtask_title')"
                                    :name="'new_subtask_title'"
                                    :placeholder="__('pages/tasks/create.field_subtask_title_placeholder')" />
                                <button type="button" x-on:click="addNewSubtasks = false" wire:click="addSubtask" class="cta-primary  self-end shrink-0 cursor-pointer" type="button">
                                    {{ __('pages/tasks/create.add_subtask') }}
                                </button>
                            </div>
                            @if (session('errorNewSubtask'))
                            <p class="text-red-500 font-bold text-sm">{{ session('errorNewSubtask') }}</p>
                            @endif


                        </div>
                    </fieldset>

                    {{-- Ressources et fichiers --}}
                    <fieldset class="flex min-w-0 flex-col gap-6 self-start overflow-hidden bg-bg-widget p-6 shadow-basic lg:col-span-1">
                        <legend class="sr-only">
                            {{ __('pages/tasks/create.resources_legend') }}
                        </legend>
                        <h3 class="text-2xl text-gold border-b border-gold pb-4 font-bold">
                            {{ __('pages/tasks/create.resources_legend') }}
                        </h3>

                        @if (count($this->form->files) > 0)
                        <ul class="flex min-w-0 flex-col gap-2" role="list">
                            @foreach ($this->form->files as $index => $file)
                            <li class="flex min-w-0 items-center justify-between gap-2 bg-bg-card p-6">
                                <a
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    href="{{ $file->temporaryUrl() }}"
                                    class="flex min-w-0 flex-1 items-center gap-3">
                                    <flux:icon name="document" class="size-5 shrink-0 text-gold" />
                                    <div class="w-0 min-w-0 flex-1 overflow-hidden">
                                        <span
                                            class="block truncate hover:text-gold transition-colors duration-150"
                                            title="{{ $file->getClientOriginalName() }}">{{ $file->getClientOriginalName() }}</span>
                                    </div>
                                </a>
                                <button
                                    type="button"
                                    wire:click="removeFile({{ $index }})"
                                    class="shrink-0 cursor-pointer hover:text-red-700/90 transition-all duration-150">
                                    <flux:icon name="trash" class="size-5" />
                                </button>
                            </li>
                            @endforeach
                        </ul>
                        @else
                        <p class="text-text-secondary">
                            {{ __('pages/tasks/create.no_files') }}
                        </p>
                        @endif
                        @error('form.files.*')
                        <p class="text-red-500 font-bold text-sm">{{ $message }}</p>
                        @enderror
                        @error('newFiles')
                        <p class="text-red-500 font-bold text-sm">{{ $message }}</p>
                        @enderror


                        <div
                            class="border border-dashed transition-colors duration-150 hover:border-gold bg-input-bg/80 duration-150"
                            :class="dragging ? 'border-gold bg-input-bg/80' : 'border-input-border bg-input-bg'"
                            x-data="{ dragging: false }"
                            x-on:click="$refs.taskCreateFilesInput.click()"
                            x-on:dragover.prevent="dragging = true"
                            x-on:dragleave="dragging = false"
                            x-on:drop.prevent="
                                dragging = false;
                                $refs.taskCreateFilesInput.files = $event.dataTransfer.files;
                                $refs.taskCreateFilesInput.dispatchEvent(new Event('change', { bubbles: true }));
                            ">

                            <label
                                for="task-files-input"
                                class="relative flex min-h-[8rem] flex-col items-center justify-center gap-3 px-4 py-8 text-center text-text-secondary cursor-pointer">
                                <flux:icon name="arrow-up-tray" class="size-10 text-text-secondary" />
                                <p class="text-sm md:text-base pointer-events-none">
                                    {{ __('pages/tasks/create.upload_drag') }}
                                    <span class="font-semibold text-gold">{{ __('pages/tasks/create.upload_browse') }}</span>
                                </p>
                                <input
                                    x-ref="taskCreateFilesInput"
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
                            {{ __('pages/tasks/create.upload_loading') }}
                        </div>
                    </fieldset>

                    {{-- Liens vidéo --}}
                    <fieldset x-data="{ addNewLink: false }" class="flex flex-col gap-6 self-start bg-bg-widget self-start p-6 shadow-basic">
                        <div class="flex gap-4 items-center justify-between border-b border-gold pb-4">
                            <legend class="sr-only">
                                {{ __('pages/tasks/create.links_legend') }}
                            </legend>
                            <h3 class="text-2xl text-gold font-bold">
                                {{ __('pages/tasks/create.links_legend') }}
                            </h3>
                            <button @click="addNewLink = true" class="cta-primary shrink-0 cursor-pointer" type="button">
                                {{ __('pages/tasks/create.add_link') }}
                            </button>
                        </div>

                        @if (count($this->form->links) > 0)
                        <ul class="flex flex-col gap-2" role="list">
                            @foreach ($this->form->links as $index => $link)
                            <li class="flex items-center bg-bg-card p-6 justify-between gap-2">
                                <span class="flex items-center gap-3">
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
                            {{ __('pages/tasks/create.no_links') }}
                        </p>
                        @endif
                        @error('form.links.*.url')
                        <p class="text-red-500 font-bold text-sm">{{ $message }}</p>
                        @enderror

                        <div x-show="addNewLink" x-cloak class="flex flex-col gap-3">
                            <x-forms.input
                                :required="false"
                                :type="'url'"
                                wire:model="newLinkUrl"
                                :label="__('pages/tasks/create.field_link_url')"
                                :name="'new_link_url'"
                                :placeholder="__('pages/tasks/create.field_link_url_placeholder')" />
                            @if (session('errorNewLink'))
                            <p class="text-red-500 font-bold text-sm">{{ session('errorNewLink') }}</p>
                            @endif
                            <x-forms.input
                                :required="false"
                                :type="'text'"
                                @keydown.enter.prevent="$wire.addLink()"
                                wire:model="newLinkTitle"
                                :label="__('pages/tasks/create.field_link_title')"
                                :name="'new_link_title'"
                                :placeholder="__('pages/tasks/create.field_link_title_placeholder')" />
                            <div class="flex justify-end">
                                <button type="button" x-on:click="addNewLink = false" wire:click="addLink" class="cta-primary shrink-0 cursor-pointer">
                                    {{ __('pages/tasks/create.add_link') }}
                                </button>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </div>
            <div class="flex flex-row justify-between gap-4 p-6 bg-bg-widget shadow-basic">
                <x-cta :href="route('tasks.index', ['slug' => currentTeam()->slug])" class="secondary" :title="__('pages/tasks/create.cancel_title')">
                    {{ __('pages/tasks/create.cancel_title') }}
                </x-cta>
                <x-forms.submit type="submit" variant="primary" :title="__('pages/tasks/create.create_title')" class="w-fit" data-test="create-team-button">
                    {{ __('pages/tasks/create.create_title') }}
                </x-forms.submit>
            </div>

        </form>
    </section>
</div>