<?php

use Livewire\Component;
use App\Models\Team;
use Livewire\Attributes\Computed;
use App\Enums\StatusTask;
use App\Models\Scrim;
use App\Enums\StatusScrim;
use App\Models\Event;

new class extends Component
{
    public Team $team;
    public function mount(): void
    {
        if (! currentMember()) {
            abort(403);
        }
        if (! currentMember()->isCoachOrStaff()) {
            abort(403);
        }
        $this->team = currentTeam();
    }
    #[Computed]
    public function membersCount(): int
    {
        return $this->team->members()->count();
    }

    #[Computed]
    public function scrimsCount(): int
    {
        return $this->team->scrims()->count();
    }

    #[Computed]
    public function tasksInProgressCount(): int
    {
        return $this->team->tasks()->where('status', StatusTask::IN_PROGRESS)->count();
    }
    #[Computed]
    public function nextScrim(): ?Scrim
    {
        return $this->team->scrims()
            ->with('opponentTeam')
            ->where('scheduled_date', '>=', now())
            ->where('status', StatusScrim::SCHEDULED)
            ->orderBy('scheduled_date', 'asc')
            ->first();
    }
    #[Computed]
    public function nextEvent(): ?Event
    {
        return $this->team->events()
            ->where('date', '>=', now())
            ->orderBy('date', 'asc')
            ->first();
    }
};
?>

<div>
    <section>
        <h2 class="text-[32px] font-bold">
            {!! __('pages/dashboard/index.coach.title', ['teamMemberName' => Auth::user()->username, 'teamName' => $this->team->name]) !!}
        </h2>
    </section>
    <div id="winrate-chart"></div>
</div>