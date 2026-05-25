<?php

use App\Models\TeamMember;
use Livewire\Attributes\Locked;
use Livewire\Component;
use App\Enums\StatusTask;
use Illuminate\Database\Eloquent\Collection;
new class extends Component
{
    #[Locked]
    public TeamMember $teamMember;
    public Collection $tasks;

    public function mount(TeamMember $teamMember): void
    {
        $this->tasks = $teamMember->tasks()
        ->with('subtasks','comments', 'links')
        ->where('team_member_id', $teamMember->id)
        ->where('status', StatusTask::IN_PROGRESS)
        ->get();
        
    }
}; ?>

<section class="flex flex-col gap-4" aria-labelledby="roster-homework-heading">
    <h3 id="roster-homework-heading" class="font-spaceGrotesk text-2xl font-bold text-white">
        {{ __('pages/roster/show.tabs.homework') }}
    </h3>

    @if ($tasks->isEmpty())
        <p class="text-base text-text-gray">{{ __('pages/roster/show.homework.empty') }}</p>
    @else
        <ul class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3" role="list">
            @foreach ($tasks as $task)
                <li wire:key="homework-task-{{ $task->id }}">
                    <x-cards.task :task="$task" />
                </li>
            @endforeach
        </ul>
    @endif
</section>
