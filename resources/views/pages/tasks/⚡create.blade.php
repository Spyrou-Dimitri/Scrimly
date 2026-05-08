<?php

use App\Enums\RoleInTeam;
use App\Enums\StatusInTeam;
use App\Models\TeamMember;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use App\Livewire\Forms\CreateTaskForm;
use Livewire\Component;

new #[Layout('layouts::team')] class extends Component
{
    public CreateTaskForm $form;
    public string $newSubTask = '';

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
            ->orderBy('id')
            ->get()
            ->map(fn(TeamMember $member): array => [
                'id' => $member->id,
                'name' => $member->user?->username ?? '',
            ])
            ->all();
    }

    public function addSubtask(): void
    {
        if (empty($this->newSubTask)) {
            session()->flash('error',__('pages/tasks/create.error_empty_subtask'));
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


    public function store(): void
    {
        $this->form->store();
        session()->flash('toast', [
            'type' => 'success',
            'message' => __('toasts/toasts.task_created'),
        ]);
        $this->redirect(route('tasks.index', ['slug' => currentTeam()->slug]));
    }

};

?>

<div>
    <section class="flex flex-col gap-8">
        <h2 class="text-2xl font-bold">
            {{ __('pages/tasks/create.title') }}
        </h2>
        <form wire:submit.prevent="store" class="flex flex-col gap-6">
            {{-- Info Principales--}}
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

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                {{-- Sous-tâches --}}
                <fieldset x-data="{ addNewSubtasks: false }" class="flex self-start flex-col gap-6 bg-bg-widget p-6 shadow-basic">
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
                    <div x-show="addNewSubtasks" class="flex items-end justify-between gap-2">
                        <x-forms.input
                            :required="false"
                            :type="'text'"
                            wire:model="newSubTask"
                            :label="__('pages/tasks/create.field_subtask_title')"
                            :name="'new_subtask_title'"
                            :placeholder="__('pages/tasks/create.field_subtask_title_placeholder')">
                            @if (session('error'))
                            <p class="text-red-500 font-bold text-sm">{{ session('error') }}</p>
                            @endif
                        </x-forms.input>
                        <button type="button" x-on:click="addNewSubtasks = false" wire:click="addSubtask" class="cta-primary shrink-0 cursor-pointer" type="button">
                            {{ __('pages/tasks/create.add_subtask') }}
                        </button>
                    </div>
                </fieldset>

                {{-- Fichiers joints --}}
                <fieldset class="flex flex-col gap-6 bg-bg-widget p-6 shadow-basic">
                    <legend class="sr-only">
                        {{ __('pages/tasks/create.resources_legend') }}
                    </legend>
                    <h3 class="text-2xl text-gold border-b border-gold pb-4 font-bold">
                        {{ __('pages/tasks/create.resources_legend') }}
                    </h3>
                    <ul class="flex flex-col gap-2" role="list"></ul>
                    <div
                        class="flex flex-col items-center justify-center gap-3 rounded border border-dashed border-input-border bg-input-bg px-4 py-8 text-center text-text-secondary">
                        <flux:icon name="arrow-up-tray" class="size-10 text-text-secondary" />
                        <p class="text-sm md:text-base">
                            {{ __('pages/tasks/create.upload_drag') }}
                            <span class="font-semibold text-gold">{{ __('pages/tasks/create.upload_browse') }}</span>
                        </p>
                        <div id="task-filepond" class="w-full" wire:ignore></div>
                    </div>
                </fieldset>
            </div>
            <x-forms.submit>
                {{ __('pages/tasks/create.submit') }}
            </x-forms.submit>
        </form>
    </section>
</div>