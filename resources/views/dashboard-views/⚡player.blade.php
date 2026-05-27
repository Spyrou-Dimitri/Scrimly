<?php

use Livewire\Component;
use App\Models\Team;
use App\Models\Event;
use App\Models\TeamMember;
use App\Models\Scrim;
use App\Enums\StatusScrim;
use App\Enums\StatusTask;
use Livewire\Attributes\Computed;

new class extends Component
{
    public bool $statsScrim = false;
    public Team $team;
    public TeamMember $member;
    public function mount(): void
    {
        if (! currentMember()) {
            abort(403);
        }
        $this->team = currentTeam();
        $this->member = currentMember();
    }
    #[Computed]
    public function averageKda(): float
    {
        if ($this->statsScrim) {
            return $this->member->overallScrimKda($this->team->id);
        }
        return $this->member->user->riotProfile->getGeneralKda();
    }
    #[Computed]
    public function winrate(): float
    {
        if ($this->statsScrim) {
            return $this->team->overallScrimWinrate();
        } else {
            return $this->member->user->riotProfile->getWinratePercentage();
        }
    }
    #[Computed]
    public function favoriteChampion(): ?string
    {
        if ($this->statsScrim) {
            return $this->member->favoriteChampionScrim();
        } else {
            return $this->member->user->riotProfile->favoriteChampion();
        }
    }
    #[Computed]
    public function totalGames(): int
    {
        if ($this->statsScrim) {
            return $this->team->scrims()
                ->whereIn('status', [StatusScrim::COMPLETED, StatusScrim::ABORTED])
                ->count();
        } else {
            return $this->member->user?->riotProfile?->totalGames() ?? 0;
        }
    }

    #[Computed]
    public function tasksInProgressCount(): int
    {
        return $this->member->tasks()->where('status', StatusTask::IN_PROGRESS)->count();
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
    <section class="flex flex-col gap-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-[32px] font-bold">
                {!! __('pages/dashboard/index.player.title', [
                    'teamName' => $this->team->name,
                    'username' => Auth::user()->username,
                ]) !!}
            </h2>
            <x-forms.radio
                wire:model.live="statsScrim"
                name="stats-scrim"
                :has-label="false"
                :fit-content="true"
                :options="[
                    ['value' => '1', 'label' => __('pages/dashboard/index.player.stats_scrim')],
                    ['value' => '0', 'label' => __('pages/dashboard/index.player.stats_game')],
                ]"
                grid-gap-class="gap-3 sm:gap-4" />
        </div>
        <div class="flex flex-row flex-wrap justify-center md:grid md:grid-cols-[repeat(13,minmax(0,1fr))] gap-6">
            <div class="w-full md:col-span-4 md:row-span-2 bg-bg-widget justify-center p-6 shadow-basic">
                <div
                    id="winrate-chart"
                    data-property='@json([
                        "value" => $this->winrate,
                        "label" => $this->statsScrim
                            ? __("pages/dashboard/index.player.winrate_scrim")
                            : __("pages/dashboard/index.player.winrate_game"),
                    ])'></div>
            </div>
            <div class="flex flex-row flex-wrap justify-center gap-6 sm:grid md:col-span-9 sm:grid-cols-9 md:row-span1">
                <div class="flex flex-col gap-2 bg-bg-widget justify-center p-6 shadow-basic w-full sm:col-span-3">
                    <p class="text-text-secondary">
                        {{ $this->statsScrim
                            ? __('pages/dashboard/index.player.average_kda_scrim')
                            : __('pages/dashboard/index.player.average_kda_game') }}
                    </p>
                    <p class="text-2xl text-center text-gold font-bold tabular-nums">
                        {{ number_format($this->averageKda, 2) }}
                    </p>
                </div>
                <x-cards.stats-dashboard
                    class="sm:col-span-3"
                    :title="$this->statsScrim
                        ? __('pages/dashboard/index.player.total_scrims')
                        : __('pages/dashboard/index.player.total_games')"
                    :value="$this->totalGames" />
                <div class="flex flex-col gap-2 bg-bg-widget justify-center p-6 shadow-basic w-full sm:col-span-3">
                    <p class="text-text-secondary">
                        {{ __('pages/dashboard/index.player.favorite_champion') }}
                    </p>
                    @if ($this->favoriteChampion)
                    <p class="text-2xl text-center text-gold font-bold">
                        {{ $this->favoriteChampion }}
                    </p>
                    @else
                    <p class="text-2xl text-center text-gold font-bold">
                        -
                    </p>
                    @endif
                </div>
            </div>
            <div class="flex flex-row flex-wrap justify-center gap-6 sm:grid md:col-span-9 sm:grid-cols-9 md:row-span-1">
                <div class="flex flex-col gap-2 bg-bg-widget justify-center p-6 shadow-basic w-full sm:col-span-3">
                    <p class="text-text-secondary">
                        {{ __('pages/dashboard/index.coach.next_scrim') }}
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
                <div class="flex flex-col gap-2 bg-bg-widget justify-center p-6 shadow-basic w-full sm:col-span-3">
                    <p class="text-text-secondary">
                        {{ __('pages/dashboard/index.coach.next_event') }}
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
                    :title="__('pages/dashboard/index.coach.tasks_count')"
                    :value="$this->tasksInProgressCount" />
            </div>
        </div>
    </section>
</div>