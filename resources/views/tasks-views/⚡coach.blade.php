<?php

use App\Models\Task;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use App\Enums\StatusTask;

new class extends Component
{
    use WithPagination;
    public string $term = '';

    public string $selected_member = '';

    public string $selected_status = '';


    private function allTasksOfTheTeamQuery(): Builder
    {
        $query = Task::query()->where('tasks.team_id', currentTeam()->id);

        if ($this->term !== '') {
            $query->where('tasks.title', 'like', '%' . $this->term . '%');
        }

        if ($this->selected_status !== '') {
            $query->where('tasks.status', $this->selected_status);
        }

        return $query;
    }

    #[Computed]
    public function allTasks()
    {
        $tasks = $this->allTasksOfTheTeamQuery()
            ->with('subtasks', 'teamMember.user');

        if ($this->selected_member !== '') {
            $tasks->whereHas('teamMember.user', function ($query) {
                $query->where('username', $this->selected_member);
            });
        }

        return $tasks->orderBy('tasks.created_at', 'desc')->paginate(8);
    }

    #[Computed]
    public function memberFilterOptions(): array
    {
        return $this->allTasksOfTheTeamQuery()
            ->whereNotNull('tasks.team_member_id')
            ->join('team_members', 'tasks.team_member_id', '=', 'team_members.id')
            ->join('users', 'team_members.user_id', '=', 'users.id')
            ->distinct()
            ->orderBy('users.username')
            ->pluck('users.username')
            ->toArray();
    }

    #[On('refresh_tasks')]
    public function refreshTasks(): void
    {
        unset($this->allTasks, $this->memberFilterOptions);
    }

    public function openModalDeleteTask(int $taskId): void
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
            'model_id' => $taskId,
        ]);
    }
};
?>

<div>
    @php
    $canManageTeam = Gate::allows('manageTeam', User::class);

    @endphp
    <section class="flex flex-col gap-6" aria-labelledby="tasks-coach-heading">
        <div class="flex flex-wrap gap-4 justify-between items-center">
            <h2 id="tasks-coach-heading" class="text-2xl font-bold">
                {{ __('pages/tasks/index.coach_title') }}
            </h2>
            @if($canManageTeam)
            <x-cta :href="route('tasks.create', ['slug' => currentTeam()->slug])" :title="__('pages/tasks/index.coach_create_task_title')" :class="'cta-primary'">
                {{ __('pages/tasks/index.coach_create_task_button') }}
            </x-cta>
            @endif
        </div>
        <div class="flex flex-col gap-4 md:flex-row md:items-end p-6 bg-bg-widget shadow-basic">
            <x-forms.input :type="'search'" wire:model.live.debounce.150ms="term" placeholder="Rechercher un devoir" :name="'searchbar'" :label="__('pages/tasks/index.coach_search_task_placeholder')" />
            <x-forms.select wire:model.live.debounce.150ms="selected_status" :name="'selected_status'" :label="__('pages/tasks/index.coach_status_placeholder')" :options="StatusTask::cases()" :disabled="__('pages/tasks/index.coach_status_placeholder_disabled')" />
            <x-forms.select wire:model.live.debounce.150ms="selected_member" :name="'selected_member'" :label="__('pages/tasks/index.coach_team_member_placeholder')"
                :options="$this->memberFilterOptions"
                :disabled="__('pages/tasks/index.coach_team_member_placeholder_disabled')" />
        </div>
        <div class="overflow-x-auto">
            <table class="w-full shadow-basic min-w-[680px]">
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
                        <td class="p-6"> <span class="{{ $task->status->macaron() }} text-sm">{{ $task->status->label() }}</span></td>
                        <td class="p-6">{{ round($task->subtasks->sum('progression') / $task->subtasks->count()) }}%</td>
                        <td class="p-6">
                            <div class="flex items-center gap-4">
                                <a href="{{ route('tasks.show', ['slug' => currentTeam()->slug, 'id' => $task->id]) }}" title="{{ __('pages/tasks/index.coach_view_task_title') }}" class="hover:text-gold transition-all duration-150">
                                    <flux:icon name="eye" class="w-5 h-5" />
                                </a>
                                @if($canManageTeam)
                                <a href="{{ route('tasks.edit', ['slug' => currentTeam()->slug, 'id' => $task->id]) }}" title="{{ __('pages/tasks/index.coach_edit_task_title') }}" class="hover:text-gold transition-all duration-150">
                                    <flux:icon name="pencil" class="w-5 h-5" />
                                </a>
                                <button wire:click="openModalDeleteTask({{ $task->id }})" title="{{ __('pages/tasks/index.coach_delete_task_title') }}" class="hover:text-red-700/90 transition-all duration-150 cursor-pointer">
                                    <flux:icon name="trash" class="w-5 h-5" />
                                </button>
                                @endif
                            </div>

                        </td>
                    </tr>
                    @endforeach
            </table>
        </div>
        {{ $this->allTasks->links() }}
    </section>
</div>