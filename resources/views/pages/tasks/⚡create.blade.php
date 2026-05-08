<?php

use App\Enums\RoleInTeam;
use App\Enums\StatusInTeam;
use App\Models\TeamMember;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::team')] class extends Component
{
    public string $title = '';

    public ?int $assigneeUserId = null;

    public ?string $dueDate = null;

    public string $description = '';

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
                'id' => $member->user_id,
                'name' => $member->user?->username ?? '',
            ])
            ->all();
    }
};

?>

<div>
    <section class="flex flex-col gap-8">
        <h2 class="text-2xl font-bold">
            {{ __('pages/tasks/create.title') }}
        </h2>
        <form class="flex flex-col gap-6">
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
                        wire:model.live="title"
                        class="w-full"
                        :label="__('pages/tasks/create.field_title')"
                        :name="'title'"
                        :placeholder="__('pages/tasks/create.field_title_placeholder')"
                        :type="'text'" />
                    <x-forms.select
                        wire:model.live="assigneeUserId"
                        class="w-full"
                        :disabled="__('pages/tasks/create.field_player_placeholder')"
                        :label="__('pages/tasks/create.field_player')"
                        :name="'assignee_user_id'"
                        :options="$this->assignablePlayers"
                        :required="true" />
                    <x-forms.input
                        wire:model.live="dueDate"
                        class="w-full"
                        :label="__('pages/tasks/create.field_due_date')"
                        :name="'due_date'"
                        :placeholder="''"
                        :required="true"
                        :type="'date'" />
                </div>
                <div class="flex flex-col gap-2">
                    <x-forms.textarea
                        wire:model.live="description"
                        :label="__('pages/tasks/create.field_description')"
                        :name="'description'"
                        :placeholder="__('pages/tasks/create.field_description_placeholder')" />
                </div>
            </fieldset>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                {{-- Sous-tâches --}}
                <fieldset class="flex flex-col gap-6 bg-bg-widget p-6 shadow-basic">
                    <div class="flex  gap-4 items-center justify-between border-b border-gold pb-4">
                        <legend class="sr-only">
                            {{ __('pages/tasks/create.subtasks_legend') }}
                        </legend>
                        <h3 class="text-2xl text-gold  font-bold">
                            {{ __('pages/tasks/create.subtasks_legend') }}
                        </h3>
                        <button class="cta-primary shrink-0 cursor-pointer" type="button">
                            {{ __('pages/tasks/create.add_subtask') }}
                        </button>

                    </div>

                    <ul class="flex flex-col gap-2" role="list"></ul>

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
        </form>
    </section>
</div>