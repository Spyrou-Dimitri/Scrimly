<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;
use App\Enums\StatusTask;
use Illuminate\Support\Facades\Auth;

new #[Layout('layouts::team')] class extends Component
{
    public string $term = '';

    public string $status = '';

    public string $selected_member = '';

    public string $selected_status = '';

    #[Computed]
    public function allTasks(): Collection
    {
        $tasks = Task::where('team_id', currentTeam()->id)->with('subtasks', 'teamMember.user', 'comments', 'links');

        //Barre de recherche
        if ($this->term !== '') {
            $tasks->where('title', 'like', '%' . $this->term . '%');
        }
        //Sélecteur de statut
        if ($this->status !== '') {
            $tasks->where('status', $this->status);
        }
        //Sélecteur de membre
        if ($this->selected_member !== '') {
            $tasks->whereHas('teamMember.user', function ($query) {
                $query->where('username', $this->selected_member);
            });
        }

        return $tasks->orderBy('created_at', 'desc')->get();
    }

    #[On('refresh_tasks')]
    public function refreshTasks(): void
    {
        unset($this->allTasks);
    }

    public function openModalDeleteTask(int $taskId): void
    {
        $this->dispatch('open_modal', [
            'form' => 'modals::tasks.delete-task',
            'model_id' => $taskId,
        ]);
    }

};
?>

<div>
    @if (currentTeam()->creator_id === Auth::user()->id || currentMember()->isCoachOrStaff())
    <section class="flex flex-col gap-6">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold">
                {{ __('pages/tasks/index.coach_title') }}
            </h2>
            <x-cta :href="route('tasks.create', ['slug' => currentTeam()->slug])" :title="__('pages/tasks/index.coach_create_task_title')" :class="'cta-primary'">
                {{ __('pages/tasks/index.coach_create_task_button') }}
            </x-cta>
        </div>
        <div class="flex flex-col gap-4 md:flex-row md:items-end p-6 bg-bg-widget shadow-basic">
            <x-forms.input :type="'search'" :term="'term'" placeholder="Rechercher un devoir" :name="'searchbar'" :label="__('pages/tasks/index.coach_search_task_placeholder')" />
            <x-forms.select wire:model.live="selected_status" :name="'selected_status'" :label="__('pages/tasks/index.coach_status_placeholder')" :options="StatusTask::cases()" :disabled="__('pages/tasks/index.coach_status_placeholder_disabled')" />
            <x-forms.select wire:model.live="selected_member" :name="'selected_member'" :label="__('pages/tasks/index.coach_team_member_placeholder')" :options="$this->allTasks->pluck('teamMember.user.username')->unique()->toArray()" :disabled="__('pages/tasks/index.coach_team_member_placeholder_disabled')" />
        </div>
        <table class="w-full shadow-basic">
            <thead class="bg-[#0D0E12]">
                <tr class="">
                    <th class="text-left p-6 ">Devoir</th>
                    <th class="text-left p-6">Assigné à</th>
                    <th class="text-left p-6">Statut</th>
                    <th class="text-left p-6">Progression</th>
                    <th class="text-left p-6">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-bg-widget">
                @foreach ($this->allTasks as $task)
                <tr>
                    <td class="p-6">
                        <p class="block truncate font-bold text-gold">{{ $task->title }} </p>
                        <p class="text-xs font-bold text-text-secondary">Echéance : @if($task->deadline) {{ $task->deadline->translatedFormat('d M Y') }} @else - @endif</p>
                    </td>
                    <td class="p-6">{{ $task->teamMember->user->username }}</td>
                    <td class="p-6"> <span class="{{ $task->status->macaron() }}">{{ $task->status->label() }}</span></td>
                    <td class="p-6">{{ $task->subtasks->sum('progression') / $task->subtasks->count() * 100 }}%</td>
                    <td class="p-6">
                        <div class="flex items-center gap-4">
                            <a href="#" title="{{ __('pages/tasks/index.coach_view_task_title') }}" class="hover:text-gold transition-all duration-150">
                                <flux:icon name="eye" class="w-5 h-5" />
                            </a>
                            <a href="{{ route('tasks.edit', ['slug' => currentTeam()->slug, 'id' => $task->id]) }}" title="{{ __('pages/tasks/index.coach_edit_task_title') }}" class="hover:text-gold transition-all duration-150">
                                <flux:icon name="pencil" class="w-5 h-5" />
                            </a>
                            <button wire:click="openModalDeleteTask({{ $task->id }})" title="{{ __('pages/tasks/index.coach_delete_task_title') }}" class="hover:text-red-700/90 transition-all duration-150 cursor-pointer">
                                <flux:icon name="trash" class="w-5 h-5" />
                            </button>
                        </div>
                        
                    </td>
                </tr>
                @endforeach
        </table>
    </section>
    @else
        @php
            $playerColumnOrder = [\App\Enums\StatusTask::DONE, \App\Enums\StatusTask::IN_PROGRESS, \App\Enums\StatusTask::TODO];
        @endphp
        <section class="flex flex-col gap-8" aria-labelledby="tasks-player-heading">
            <h2 id="tasks-player-heading" class="text-2xl font-bold">{{ __('pages/tasks/index.player_title') }}</h2>
            <div class="grid gap-6 md:grid-cols-3 md:gap-8">
                @foreach ($playerColumnOrder as $columnStatus)
                    <div class="flex min-w-0 flex-col gap-4">
                        <div class="flex flex-wrap items-center gap-3 pb-4 border-b-2 {{ $columnStatus->borderColor() }}">
                            <h3 class="min-w-0 flex-1 text-lg font-semibold pl-4 border-l-8 {{ $columnStatus->borderColor() }} text-white">{{ $columnStatus->label() }}</h3>
                            <div class="flex items-center justify-between bg-bg-widget px-3 py-1">
                                <p class="{{ $columnStatus->textColor() }} font-bold">
                                    {{ $this->allTasks->where('status', $columnStatus)->count() }}
                                </p>
                            </div>
                        </div>
                        <ul class="flex min-h-40 flex-col gap-4 pb-6" role="list">
                            @foreach ($this->allTasks->where('status', $columnStatus) as $task)
                                <li>
                                    <x-cards.task :task="$task" />
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

</div>
