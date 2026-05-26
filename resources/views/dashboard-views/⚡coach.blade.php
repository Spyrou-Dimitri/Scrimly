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
    {{ $this->membersCount }} membres
    {{ $this->scrimsCount }} scrims
    {{ $this->tasksInProgressCount }} tâches en cours
    {{ $this->nextScrim ? 'Prochain scrim : ' . $this->nextScrim->opponentTeam->name : 'Aucun scrim programmé' }}
    {{ $this->nextEvent ? 'Prochain événement : ' . $this->nextEvent->title : 'Aucun événement programmé' }}

</div>