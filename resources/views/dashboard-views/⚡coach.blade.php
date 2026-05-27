<?php

use Livewire\Component;
use App\Models\Team;
use Livewire\Attributes\Computed;
use App\Enums\StatusTask;
use App\Models\Scrim;
use App\Enums\StatusScrim;
use App\Models\Event;
use App\Enums\StatusApplication;
use App\Enums\ScrimOutcome;
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
    #[Computed]
    public function winrateInScrims(): float
    {
        $allScrims = $this->team->scrims()->count();
        $winScrims = $this->team->scrims()->where('outcome', ScrimOutcome::Victory)->count();
        return round($winScrims / $allScrims * 100, 1);
    }
    #[Computed]
    public function teamApplicationsCount(): int
    {
        return $this->team->teamApplications()->where('status', StatusApplication::PENDING)->count();
    }
};
?>

<div>
    <section class="flex flex-col gap-6">
        <h2 class="text-[32px] font-bold">
            {!! __('pages/dashboard/index.coach.title', ['teamMemberName' => Auth::user()->username, 'teamName' => $this->team->name]) !!}
        </h2>
        <div class="flex flex-row flex-wrap justify-center md:grid md:grid-cols-13 gap-6">
            <h3 class="sr-only">
                {{ __('pages/dashboard/index.coach.quick_stats') }}
            </h3>
            <div class=" w-full md:col-span-4 md:row-span-2 bg-bg-widget justify-center p-6 shadow-basic">
                <div class="" id="winrate-chart" data-property="@json($this->winrateInScrims)"></div>
            </div>
            <div class="flex flex-row flex-wrap justify-center gap-6 sm:grid md:col-span-9 sm:grid-cols-9 md:row-span1">
                <x-cards.stats-dashboard
                    class="sm:col-span-3"
                    :title="__('pages/dashboard/index.coach.members_count')"
                    :value="$this->membersCount" />
                <x-cards.stats-dashboard
                    class="sm:col-span-3"
                    :title="__('pages/dashboard/index.coach.scrims_count')"
                    :value="$this->scrimsCount" />
                <x-cards.stats-dashboard
                    class="sm:col-span-3"
                    :title="__('pages/dashboard/index.coach.tasks_count')"
                    :value="$this->tasksInProgressCount" />
            </div>
            <div class="flex flex-row flex-wrap justify-center gap-6 sm:grid md:col-span-9 sm:grid-cols-9 md:row-span-1">
                <div class="flex flex-col gap-2 bg-bg-widget justify-center p-6 shadow-basic  w-full sm:col-span-3">
                    <p class="text-text-secondary ">
                        {{__('pages/dashboard/index.coach.next_scrim')}}
                    </p>
                    @if ($this->nextScrim)
                    <p class="text-2xl text-center text-gold font-bold">
                        {{ $this->nextScrim->opponentTeam->name }}
                    </p>
                    @else
                    <p class="text-2xl text-center text-gold font-bold">
                        -
                    </p>
                    @endif
                </div>
                <div class="flex flex-col gap-2 bg-bg-widget justify-center p-6 shadow-basic  w-full sm:col-span-3">
                    <p class="text-text-secondary ">
                        {{__('pages/dashboard/index.coach.next_event')}}
                    </p>
                    @if ($this->nextEvent)
                    <p class="text-2xl text-center text-gold font-bold">
                        {{ $this->nextEvent->title }}
                    </p>
                    @else
                    <p class="text-2xl text-center text-gold font-bold">
                        -
                    </p>
                    @endif
                </div>
                <x-cards.stats-dashboard
                    class="sm:col-span-3"
                    :title="__('pages/dashboard/index.coach.team_applications_count')"
                    :value="$this->teamApplicationsCount" />
            </div>

        </div>
        <div>
            <h3 class="sr-only">
                {{ __('pages/dashboard/index.coach.quick_actions') }}
            </h3>
        </div>

    </section>
</div>