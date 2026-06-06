<?php

use Livewire\Component;
use App\Models\Team;
use App\Models\Event;
use App\Models\TeamMember;
use App\Models\Scrim;
use App\Enums\StatusScrim;
use App\Enums\StatusTask;
use App\Models\ScrimGamePlayer;
use Illuminate\Support\Collection;
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

    #[Computed]
    public function nextScrims(): Collection
    {
        return $this->team->scrims()
            ->with('opponentTeam')
            ->where('scheduled_date', '>=', now())
            ->where('status', StatusScrim::SCHEDULED)
            ->orderBy('scheduled_date', 'desc')
            ->limit(3)
            ->get();
    }

    #[Computed]
    public function lastScrimsResult(): Collection
    {
        return $this->team->scrims()
            ->with([
                'opponentTeam',
                'scrimGames.scrimGamePlayers' => fn ($query) => $query->where('team_member_id', $this->member->id),
            ])
            ->withCount([
                'scrimGames as wins_count' => fn ($query) => $query->where('is_victory', true),
                'scrimGames as losses_count' => fn ($query) => $query->where('is_victory', false),
                'scrimGames as games_count',
            ])
            ->whereIn('status', [StatusScrim::COMPLETED, StatusScrim::ABORTED])
            ->orderBy('updated_at', 'desc')
            ->limit(3)
            ->get();
    }
    public function scrimPlayerStats(Scrim $scrim): array
    {
        $scrimGamePlayers = ScrimGamePlayer::query()
            ->where('team_member_id', $this->member->id)
            ->whereHas('scrimGame', fn ($query) => $query->where('scrim_id', $scrim->id))
            ->get();
        $totalKda = $scrimGamePlayers->sum('kills') + $scrimGamePlayers->sum('assists');
        $totalDeaths = $scrimGamePlayers->sum('deaths');
        $averageKda = $totalDeaths === 0 ? $totalKda : round($totalKda / $totalDeaths, 2);

        return [
            'average_kda' => $averageKda,
            'champions' => $scrimGamePlayers->pluck('champion')->unique()->values(),
            'games_count' => $scrimGamePlayers->count(),
        ];
    }

    #[Computed]
    public function tasksInProgress(): Collection
    {
        return $this->member->tasks()
            ->with('subtasks')
            ->where('status', StatusTask::IN_PROGRESS)
            ->orderBy('updated_at', 'desc')
            ->limit(3)
            ->get();
    }

    #[Computed]
    public function tasksToDo(): Collection
    {
        return $this->member->tasks()
            ->with('subtasks')
            ->where('status', StatusTask::TODO)
            ->orderBy('updated_at', 'desc')
            ->limit(3)
            ->get();
    }
};
?>

<div>
    <section class="flex flex-col gap-6" aria-labelledby="dashboard-player-heading">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 id="dashboard-player-heading" class="text-[32px] font-bold">
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
                    x-data="winrateChart"
                    data-property='@json([
                        "value" => $this->winrate,
                        "label" => $this->statsScrim
                            ? __("pages/dashboard/index.player.winrate_scrim")
                            : __("pages/dashboard/index.player.winrate_game"),
                    ])'>
                    <div x-ref="chart" wire:ignore></div>
                </div>
            </div>
            <div class="flex flex-row flex-wrap justify-center gap-6 sm:grid md:col-span-9 sm:grid-cols-9 md:row-span1" role="list">
                <div class="flex flex-col gap-2 bg-bg-widget justify-center p-6 shadow-basic w-full sm:col-span-3" role="listitem">
                    <p class="text-text-secondary">
                        {{ $this->statsScrim
                            ? __('pages/dashboard/index.player.average_kda_scrim')
                            : __('pages/dashboard/index.player.average_kda_game') }}
                    </p>
                    <p class="text-[40px] text-center text-gold font-bold ">
                        {{ number_format($this->averageKda, 2) }}
                    </p>
                </div>
                <x-cards.stats-dashboard
                    role="listitem"
                    class="sm:col-span-3"
                    :title="$this->statsScrim
                        ? __('pages/dashboard/index.player.total_scrims')
                        : __('pages/dashboard/index.player.total_games')"
                    :value="$this->totalGames" />
                <div class="flex flex-col gap-2 bg-bg-widget justify-center p-6 shadow-basic w-full sm:col-span-3" role="listitem">
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
            <div class="flex flex-row flex-wrap justify-center gap-6 sm:grid md:col-span-9 sm:grid-cols-9 md:row-span-1" role="list">
                <div class="flex flex-col gap-2 bg-bg-widget justify-center p-6 shadow-basic w-full sm:col-span-3" role="listitem">
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
                <div class="flex flex-col gap-2 bg-bg-widget justify-center p-6 shadow-basic w-full sm:col-span-3" role="listitem">
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
                    role="listitem"
                    class="sm:col-span-3"
                    :title="__('pages/dashboard/index.coach.tasks_count')"
                    :value="$this->tasksInProgressCount" />
            </div>
        </div>
        @php
        $ddragonVersion = config('riot.ddragon_version');
        @endphp

        <div class="grid grid-cols-1 gap-6 md:grid-cols-[repeat(13,minmax(0,1fr))]">
            <div class="flex flex-col gap-6 md:col-span-7">
                <x-accordion
                    :title="__('pages/dashboard/index.player.widgets.next_scrims_title')"
                    :open="true"
                    :count="$this->nextScrims->count()"
                    heading-level="h3">
                    @if ($this->nextScrims->isEmpty())
                    <li class="col-span-12">
                        <p class="text-sm text-text-secondary">
                            {{ __('pages/dashboard/index.player.widgets.empty_next_scrims') }}
                        </p>
                    </li>
                    @else
                    @foreach ($this->nextScrims as $scrim)
                    @php
                    $opponent = $scrim->opponentTeam;
                    $scheduledAt = $scrim->scheduled_at;
                    @endphp
                    <li class="col-span-12" wire:key="player-dashboard-next-scrim-{{ $scrim->id }}">
                        <article x-on:click="$el.querySelector('[data-scrim-link]')?.click()" class="relative cursor-pointer flex flex-col border-l-2 border-gold bg-bg-card p-4 basic-shadow md:p-5 card-animated-border">
                            <span class="card-animated-border-right-edge" aria-hidden="true"></span>
                            <div class="relative z-[1] flex flex-col gap-4 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between">
                                <div class="flex flex-1 items-center gap-4 md:gap-6">
                                    <div class="flex w-[4.25rem] shrink-0 flex-col items-center gap-1 border-r border-white/10 md:w-[4.75rem]">
                                        <p class="text-xs font-semibold uppercase text-white">{{ $scheduledAt->translatedFormat('M') }}</p>
                                        <p class="text-2xl font-bold text-white">{{ $scheduledAt->format('j') }}</p>
                                        <span class="h-0.5 w-8 shrink-0 bg-gold" aria-hidden="true"></span>
                                    </div>
                                    <div class="flex flex-1 items-center gap-3 md:gap-4">
                                        <x-team-logo
                                            :team="$opponent"
                                            preset="scrim-row"
                                            class="size-14 shrink-0 object-cover md:size-16"
                                        />
                                        <div class="flex flex-col gap-1">
                                            <h4 class="truncate text-xl font-bold text-white">{{ $opponent->name }}</h4>
                                            <p class="text-sm text-text-secondary">
                                                {{ __('pages/scrims/index.upcoming_format_time', ['games' => $scrim->number_of_games, 'time' => $scheduledAt->format('H:i')]) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <a
                                    wire:navigate
                                    data-scrim-link
                                    href="{{ route('scrims.show', ['slug' => currentTeam()->slug, 'id' => $scrim->id]) }}"
                                    class="cta-primary block md:w-full xl:w-auto"
                                    title="{{ __('pages/dashboard/index.player.widgets.show_scrim') }}">
                                    {{ __('pages/dashboard/index.player.widgets.show_scrim') }}
                                </a>
                            </div>
                        </article>
                    </li>
                    @endforeach
                    @endif
                </x-accordion>
                <x-accordion
                    :title="__('pages/dashboard/index.player.widgets.last_scrims_result_title')"
                    :open="true"
                    :count="$this->lastScrimsResult->count()"
                    heading-level="h3">
                    @if ($this->lastScrimsResult->isEmpty())
                    <li class="col-span-12">
                        <p class="text-sm text-text-secondary">
                            {{ __('pages/dashboard/index.player.widgets.empty_last_scrims_result') }}
                        </p>
                    </li>
                    @else
                    @foreach ($this->lastScrimsResult as $scrim)
                    @php
                    $opponent = $scrim->opponentTeam;
                    $scrimStats = $this->scrimPlayerStats($scrim);
                    @endphp
                    <li class="col-span-12" wire:key="player-dashboard-last-scrim-{{ $scrim->id }}">
                        <article x-on:click="$el.querySelector('[data-scrim-link]')?.click()" class="relative cursor-pointer flex flex-col border-l-2 border-gold bg-bg-card p-4 basic-shadow md:p-5 card-animated-border">
                            <span class="card-animated-border-right-edge" aria-hidden="true"></span>
                            <div class="relative z-[1] flex flex-col gap-4">
                                <div class="flex flex-col gap-4 sm:flex-row flex-wrap sm:items-center sm:justify-between">
                                    <div class="flex flex-1 items-center gap-3 md:gap-4">
                                        @if ($opponent)
                                        <x-team-logo
                                            :team="$opponent"
                                            preset="scrim-row"
                                            :alt="$opponent->name"
                                            class="size-14 shrink-0 object-cover md:size-16"
                                        />
                                        @endif
                                        <div class="flex flex-1 flex-wrap items-center gap-4">
                                            <h4 class="truncate text-xl font-bold text-white">
                                                {{ $opponent?->name ?? __('pages/scrims/index.upcoming_opponent_unknown') }}
                                            </h4>
                                            <div class="flex shrink-0 flex-wrap items-center gap-2">
                                                @if ($scrim->outcome)
                                                <span class="{{ $scrim->outcome->macaron() }}">{{ $scrim->outcome->label() }}</span>
                                                @endif
                                                @if ($scrim->games_count > 0)
                                                <span class="font-bold  text-sm">
                                                    <span class="text-victory">{{ $scrim->wins_count }}</span>
                                                    <span class="text-text-secondary"> - </span>
                                                    <span class="text-defeat">{{ $scrim->losses_count }}</span>
                                                </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <a
                                        wire:navigate
                                        data-scrim-link
                                        class="cta-primary md:w-full xl:w-auto"
                                        href="{{ route('scrims.show', ['slug' => currentTeam()->slug, 'id' => $scrim->id]) }}"
                                        title="{{ __('pages/dashboard/index.player.widgets.show_scrim') }}">
                                        {{ __('pages/dashboard/index.player.widgets.show_scrim') }}
                                    </a>
                                </div>

                                @if ($scrimStats['games_count'] > 0)
                                <div class="flex flex-col gap-4 border-t border-white/10 pt-4 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="flex flex-col gap-1">
                                        <p class="text-sm text-text-secondary">
                                            {{ __('pages/dashboard/index.player.widgets.average_kda') }}
                                        </p>
                                        <p class="text-lg text-gold font-bold">
                                            {{ number_format($scrimStats['average_kda'], 2) }}
                                        </p>
                                    </div>

                                    @if ($scrimStats['champions']->isNotEmpty())
                                    <div class="flex flex-col gap-2">
                                        <p class="text-sm text-text-secondary">
                                            {{ __('pages/dashboard/index.player.widgets.champions_played') }}
                                        </p>
                                        <div class="flex flex-wrap items-center gap-2">
                                            @foreach ($scrimStats['champions'] as $champion)
                                            <img
                                                src="https://ddragon.leagueoflegends.com/cdn/{{ $ddragonVersion }}/img/champion/{{ $champion }}.png"
                                                alt="{{ $champion }}"
                                                title="{{ $champion }}"
                                                class="aspect-square size-10 shrink-0 rounded border border-[#2C2D34] bg-bg-card/60 md:size-12">
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif
                                </div>
                                @endif
                            </div>
                        </article>
                    </li>
                    @endforeach
                    @endif
                </x-accordion>
            </div>

            <div class="flex flex-col gap-6 md:col-span-6">
                <x-accordion
                    :title="__('pages/dashboard/index.player.widgets.tasks_in_progress_title')"
                    :open="true"
                    :count="$this->tasksInProgress->count()"
                    heading-level="h3">
                    @if ($this->tasksInProgress->isEmpty())
                    <li class="col-span-12">
                        <p class="text-sm text-text-secondary">
                            {{ __('pages/dashboard/index.player.widgets.empty_tasks_in_progress') }}
                        </p>
                    </li>
                    @else
                    @foreach ($this->tasksInProgress as $task)
                    @php
                    $completedSubtasks = $task->subtasks->where('is_completed', true)->count();
                    $totalSubtasks = $task->subtasks->count();
                    $progressPercent = $totalSubtasks > 0 ? round($completedSubtasks / $totalSubtasks * 100) : 0;
                    @endphp
                    <li class="col-span-12" wire:key="player-dashboard-task-in-progress-{{ $task->id }}">
                        <article
                            x-on:click="$el.querySelector('[data-task-link]')?.click()"
                            @class([
                                'relative cursor-pointer flex flex-col border-l-2 bg-bg-card p-4 basic-shadow md:p-5 card-animated-border',
                                $task->status->borderColor(),
                            ])
                            data-task-status="{{ $task->status->value }}">
                            <a data-task-link wire:navigate href="{{ route('tasks.show', ['slug' => currentTeam()->slug, 'id' => $task->id]) }}" class="sr-only">
                                {{ $task->title }}
                            </a>
                            <span class="card-animated-border-right-edge" aria-hidden="true"></span>
                            <div class="relative z-[1] flex flex-col gap-4">
                                <h4 class="text-xl font-bold text-gold">{{ $task->title }}</h4>
                                <div class="flex flex-col gap-3">
                                    <div class="flex justify-between gap-2">
                                        <p class="text-sm text-text-secondary">
                                            {{ __('pages/dashboard/index.player.widgets.task_progress') }} : {{ $completedSubtasks }} / {{ $totalSubtasks }}
                                        </p>
                                        <p class="text-sm text-text-secondary">{{ $progressPercent }}%</p>
                                    </div>
                                    <div class="h-2.5 w-full bg-gray-200">
                                        <div class="{{ $task->status->backgroundColor() }} h-2.5" style="width: {{ $progressPercent }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </article>
                    </li>
                    @endforeach
                    @endif
                </x-accordion>

                <x-accordion
                    :title="__('pages/dashboard/index.player.widgets.tasks_to_do_title')"
                    :open="true"
                    :count="$this->tasksToDo->count()"
                    heading-level="h3">
                    @if ($this->tasksToDo->isEmpty())
                    <li class="col-span-12">
                        <p class="text-sm text-text-secondary">
                            {{ __('pages/dashboard/index.player.widgets.empty_tasks_to_do') }}
                        </p>
                    </li>
                    @else
                    @foreach ($this->tasksToDo as $task)
                    @php
                    $completedSubtasks = $task->subtasks->where('is_completed', true)->count();
                    $totalSubtasks = $task->subtasks->count();
                    $progressPercent = $totalSubtasks > 0 ? round($completedSubtasks / $totalSubtasks * 100) : 0;
                    @endphp
                    <li class="col-span-12" wire:key="player-dashboard-task-to-do-{{ $task->id }}">
                        <article
                            x-on:click="$el.querySelector('[data-task-link]')?.click()"
                            @class([
                                'relative cursor-pointer flex flex-col border-l-2 bg-bg-card p-4 basic-shadow md:p-5 card-animated-border',
                                $task->status->borderColor(),
                            ])
                            data-task-status="{{ $task->status->value }}">
                            <a data-task-link wire:navigate href="{{ route('tasks.show', ['slug' => currentTeam()->slug, 'id' => $task->id]) }}" class="sr-only">
                                {{ $task->title }}
                            </a>
                            <span class="card-animated-border-right-edge" aria-hidden="true"></span>
                            <div class="relative z-[1] flex flex-col gap-4">
                                <h4 class="text-xl font-bold text-gold">{{ $task->title }}</h4>
                                <div class="flex flex-col gap-3">
                                    <div class="flex justify-between gap-2">
                                        <p class="text-sm text-text-secondary">
                                            {{ __('pages/dashboard/index.player.widgets.task_progress') }} : {{ $completedSubtasks }} / {{ $totalSubtasks }}
                                        </p>
                                        <p class="text-sm text-text-secondary">{{ $progressPercent }}%</p>
                                    </div>
                                    <div class="h-2.5 w-full bg-gray-200">
                                        <div class="{{ $task->status->backgroundColor() }} h-2.5" style="width: {{ $progressPercent }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </article>
                    </li>
                    @endforeach
                    @endif
                </x-accordion>
            </div>
        </div>
    </section>
</div>