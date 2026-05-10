<?php

use Livewire\Component;
use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;

new class extends Component
{
    public Collection $allTasks;
    public function mount()
    {
        $member = currentMember();
        if (!$member) {
            return redirect()->route('home');
        }
        $this->allTasks = Task::where('team_id', currentTeam()->id)
            ->with('subtasks','comments', 'links')
            ->where('team_member_id', $member->id)
            ->get();
    }
};
?>

<div>
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
</div>