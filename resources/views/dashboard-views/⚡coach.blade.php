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
use Illuminate\Support\Collection;
use App\Enums\StatusScrimRequest;
use Carbon\Carbon;

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
        return $this->team->overallScrimWinrate();
    }
    #[Computed]
    public function teamApplicationsCount(): int
    {
        return $this->team->teamApplications()->where('status', StatusApplication::PENDING)->count();
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
    public function newScrimRequests(): Collection
    {
        return $this->team->receivedScrimRequests()
            ->with('requesterTeam')
            ->where('status', StatusScrimRequest::PENDING)
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();
    }
    #[Computed]
    public function recentUpdatedTasks(): Collection
    {
        return $this->team->tasks()
            ->with(['subtasks', 'teamMember.user'])
            ->where('status', StatusTask::IN_PROGRESS)
            ->orderBy('updated_at', 'desc')
            ->limit(3)
            ->get();
    }
    #[Computed]
    public function lastScrimsResult(): Collection
    {
        return $this->team->scrims()
            ->with('opponentTeam')
            ->withCount([
                'scrimGames as wins_count' => fn($query) => $query->where('is_victory', true),
                'scrimGames as losses_count' => fn($query) => $query->where('is_victory', false),
                'scrimGames as games_count',
            ])
            ->whereIn('status', [StatusScrim::COMPLETED, StatusScrim::ABORTED])
            ->orderBy('updated_at', 'desc')
            ->limit(3)
            ->get();
    }
    #[Computed]
    public function lastEvents(): Collection
    {
        return $this->team->events()
            ->where('date', '>=', now())
            ->orderBy('date', 'asc')
            ->limit(3)
            ->get();
    }

    public function showScrimRequest(int $scrimRequestId): void
    {
        $this->dispatch('open_modal', [
            'form' => 'scrims.show-scrim-request',
            'model_id' => $scrimRequestId,
        ]);
    }
};
?>

<div>
    <section class="flex flex-col gap-6">
        <h2 class="text-[32px] font-bold">
            {!! __('pages/dashboard/index.coach.title', ['teamMemberName' => Auth::user()->username, 'teamName' => $this->team->name]) !!}
        </h2>
        <div class="flex flex-row flex-wrap justify-center md:grid md:grid-cols-[repeat(13,minmax(0,1fr))] gap-6">

            <div class=" w-full md:col-span-4 md:row-span-2 bg-bg-widget justify-center p-6 shadow-basic">
                <div x-data="winrateChart" data-property='@json([
                    "value" => $this->winrateInScrims,
                    "label" => __("pages/dashboard/index.coach.winrate")])'>
                    <div x-ref="chart" wire:ignore></div>
                </div>
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
        <div class="grid grid-cols-1 gap-6 md:grid-cols-[repeat(13,minmax(0,1fr))]">


            <div class="flex flex-col gap-6 md:col-span-7">
                <x-accordion
                    :title="__('pages/dashboard/index.coach.widgets.next_scrims_title')"
                    :open="true"
                    :count="$this->nextScrims->count()"
                    heading-level="h3">
                    @if ($this->nextScrims->isEmpty())
                    <li class="col-span-12">
                        <p class="text-sm text-text-secondary">
                            {{ __('pages/dashboard/index.coach.widgets.empty_next_scrims') }}
                        </p>
                    </li>
                    @else
                    @foreach ($this->nextScrims as $scrim)
                    @php
                    $opponent = $scrim->opponentTeam;
                    $scheduledAt = $scrim->scheduled_at;
                    @endphp
                    <li class="col-span-12" wire:key="dashboard-next-scrim-{{ $scrim->id }}">
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
                                    title="{{ __('pages/dashboard/index.coach.widgets.show_scrim') }}">
                                    {{ __('pages/dashboard/index.coach.widgets.show_scrim') }}
                                </a>
                            </div>
                        </article>
                    </li>
                    @endforeach
                    @endif
                </x-accordion>

                <x-accordion
                    :title="__('pages/dashboard/index.coach.widgets.last_scrims_result_title')"
                    :open="true"
                    :count="$this->lastScrimsResult->count()"
                    heading-level="h3">
                    @if ($this->lastScrimsResult->isEmpty())
                    <li class="col-span-12">
                        <p class="text-sm text-text-secondary">
                            {{ __('pages/dashboard/index.coach.widgets.empty_last_scrims_result') }}
                        </p>
                    </li>
                    @else
                    @foreach ($this->lastScrimsResult as $scrim)
                    @php
                    $opponent = $scrim->opponentTeam;
                    @endphp
                    <li class="col-span-12" wire:key="dashboard-last-scrim-{{ $scrim->id }}">
                        <article x-on:click="$el.querySelector('[data-scrim-link]')?.click()" class="relative cursor-pointer flex flex-col border-l-2 border-gold bg-bg-card p-4 basic-shadow md:p-5 card-animated-border">
                            <span class="card-animated-border-right-edge" aria-hidden="true"></span>
                            <div class="relative z-[1] flex flex-col gap-4 sm:flex-row flex-wrap sm:items-center sm:justify-between">
                                <div class="flex flex-1 items-center gap-3 md:gap-4">
                                    @if ($opponent)
                                    <x-team-logo
                                        :team="$opponent"
                                        preset="scrim-row"
                                        :alt="$opponent->name"
                                        class="size-14 shrink-0 object-cover md:size-16"
                                    />
                                    @endif
                                    <div class="flex flex-1 flex-wrap items-center gap-x-3 gap-y-1">
                                        <h4 class="truncate text-xl font-bold text-white">
                                            {{ $opponent?->name ?? __('pages/scrims/index.upcoming_opponent_unknown') }}
                                        </h4>
                                        <div class="flex shrink-0 flex-wrap items-center gap-2">
                                            @if ($scrim->outcome)
                                            <span class="{{ $scrim->outcome->macaron() }}">{{ $scrim->outcome->label() }}</span>
                                            @endif
                                            @if ($scrim->games_count > 0)
                                            <span class="font-bold tabular-nums text-sm">
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
                                    title="{{ __('pages/dashboard/index.coach.widgets.show_scrim') }}">
                                    {{ __('pages/dashboard/index.coach.widgets.show_scrim') }}
                                </a>
                            </div>
                        </article>
                    </li>
                    @endforeach
                    @endif
                </x-accordion>

                <x-accordion
                    :title="__('pages/dashboard/index.coach.widgets.new_scrim_requests_title')"
                    :open="true"
                    :count="$this->newScrimRequests->count()"
                    heading-level="h3">
                    @if ($this->newScrimRequests->isEmpty())
                    <li class="col-span-12">
                        <p class="text-sm text-text-secondary">
                            {{ __('pages/dashboard/index.coach.widgets.empty_new_scrim_requests') }}
                        </p>
                    </li>
                    @else
                    @foreach ($this->newScrimRequests as $request)
                    @php
                    $otherTeam = $request->requesterTeam;
                    $scheduledAt = Carbon::parse($request->scheduled_date->format('Y-m-d').' '.$request->scheduled_time);
                    @endphp
                    <li class="col-span-12" wire:key="dashboard-scrim-request-{{ $request->id }}">
                        <article x-on:click="$el.querySelector('[data-scrim-request-link]')?.click()" class="relative cursor-pointer flex flex-col border-l-2 border-gold bg-bg-card p-4 basic-shadow md:p-5 card-animated-border">
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
                                            :team="$otherTeam"
                                            preset="scrim-row"
                                            class="size-14 shrink-0 rounded object-cover md:size-16"
                                        />
                                        <div>
                                            <h4 class="truncate text-[20px] font-bold text-white">{{ $otherTeam->name }}</h4>
                                            <p class="mt-1 text-sm text-text-secondary">
                                                BO{{ $request->number_of_games }}
                                                @if ($scheduledAt->isTomorrow())
                                                • {{ __('pages/scrims/index.schedule_tomorrow', ['time' => $scheduledAt->format('H:i')]) }}
                                                @elseif ($scheduledAt->isToday())
                                                • {{ __('pages/scrims/index.schedule_today', ['time' => $scheduledAt->format('H:i')]) }}
                                                @else
                                                • {{ __('pages/scrims/index.schedule_date', ['date' => $scheduledAt->translatedFormat('j F'), 'time' => $scheduledAt->format('H:i')]) }}
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <button data-scrim-request-link wire:click="showScrimRequest({{ $request->id }})" type="button" class="cta-primary block w-full  px-4 py-2 text-center md:w-full xl:w-fit sm:w-auto">
                                    {{ __('pages/dashboard/index.coach.widgets.show_request') }}
                                </button>
                            </div>
                        </article>
                    </li>
                    @endforeach
                    @endif
                </x-accordion>
            </div>

            <div class="flex flex-col gap-6 md:col-span-6">
                <x-accordion
                    :title="__('pages/dashboard/index.coach.widgets.last_events_title')"
                    :open="true"
                    :count="$this->lastEvents->count()"
                    heading-level="h3">
                    @if ($this->lastEvents->isEmpty())
                    <li class="col-span-12">
                        <p class="text-sm text-text-secondary">
                            {{ __('pages/dashboard/index.coach.widgets.empty_last_events') }}
                        </p>
                    </li>
                    @else
                    @foreach ($this->lastEvents as $event)
                    @php
                    $eventDate = Carbon::parse($event->date);
                    @endphp
                    <li class="col-span-12" wire:key="dashboard-event-{{ $event->id }}">
                        <article class="relative flex flex-col bg-bg-card p-4 basic-shadow md:p-5">
                            <div class="relative z-[1] flex flex-col gap-4 sm:flex-row sm:items-center">
                                <div class="flex flex-1 items-center gap-4 md:gap-6">
                                    <div class="flex w-[4.25rem] shrink-0 flex-col items-center gap-1 border-r border-white/10 md:w-[4.75rem]">
                                        <p class="text-xs font-semibold uppercase text-white">{{ $eventDate->translatedFormat('M') }}</p>
                                        <p class="text-2xl font-bold text-white">{{ $eventDate->format('j') }}</p>
                                        <span class="h-0.5 w-8 shrink-0 bg-gold" aria-hidden="true"></span>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="truncate text-xl font-bold text-white">{{ $event->title }}</h4>
                                        <p class="mt-1 text-sm text-text-secondary">
                                            @if ($event->all_day)
                                            {{ __('modals/calendar/show-a-day.event_format', [
                                                    'time' => __('modals/calendar/create-event.all_day'),
                                                    'type' => $event->type->label(),
                                                ]) }}
                                            @else
                                            {{ __('modals/calendar/show-a-day.event_format', [
                                                    'time' => Carbon::parse($event->start_time)->format('H:i') . ' – ' . Carbon::parse($event->end_time)->format('H:i'),
                                                    'type' => $event->type->label(),
                                                ]) }}
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </article>
                    </li>
                    @endforeach
                    @endif
                </x-accordion>

                <x-accordion
                    :title="__('pages/dashboard/index.coach.widgets.recent_tasks_title')"
                    :open="true"
                    :count="$this->recentUpdatedTasks->count()"
                    heading-level="h3">
                    @if ($this->recentUpdatedTasks->isEmpty())
                    <li class="col-span-12">
                        <p class="text-sm text-text-secondary">
                            {{ __('pages/dashboard/index.coach.widgets.empty_recent_tasks') }}
                        </p>
                    </li>
                    @else
                    @foreach ($this->recentUpdatedTasks as $task)
                    @php
                    $completedSubtasks = $task->subtasks->where('is_completed', true)->count();
                    $totalSubtasks = $task->subtasks->count();
                    $progressPercent = $totalSubtasks > 0 ? round($completedSubtasks / $totalSubtasks * 100) : 0;
                    @endphp
                    <li class="col-span-12" wire:key="dashboard-task-{{ $task->id }}">
                        <article
                            x-on:click="$el.querySelector('[data-task-link]')?.click()"
                            @class([ 'relative cursor-pointer flex flex-col border-l-2 bg-bg-card p-4 basic-shadow md:p-5 card-animated-border' ,
                            $task->status->borderColor(),
                            ])
                            data-task-status="{{ $task->status->value }}">
                            <a data-task-link wire:navigate href="{{ route('tasks.show', ['slug' => currentTeam()->slug, 'id' => $task->id]) }}" class="sr-only">
                                {{ $task->title }}
                            </a>
                            <span class="card-animated-border-right-edge" aria-hidden="true"></span>
                            <div class="relative z-[1] flex flex-col gap-4">
                                <div class="flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between sm:gap-4">
                                    <h4 class="text-xl font-bold text-gold">{{ $task->title }}</h4>
                                    <p class="shrink-0 text-sm text-text-secondary">
                                        {{ __('pages/dashboard/index.coach.widgets.task_assigned_to', ['member' => $task->teamMember?->user?->username ?? '-']) }}
                                    </p>
                                </div>
                                <div class="flex flex-col gap-3">
                                    <div class="flex justify-between gap-2">
                                        <p class="text-sm text-text-secondary">
                                            {{ __('pages/dashboard/index.coach.widgets.task_progress') }} : {{ $completedSubtasks }} / {{ $totalSubtasks }}
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