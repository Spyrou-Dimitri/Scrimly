<?php

use App\Enums\StatusScrim;
use App\Enums\StatusScrimRequest;
use App\Models\ScrimRequest;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Scrim;
use Illuminate\Support\Facades\Gate;
new #[Layout('layouts::team')] class extends Component
{
    #[On('refresh_scrims')]
    public function refreshScrimRequests(): void
    {
        unset($this->receivedScrimRequests, $this->sentScrimRequests, $this->scrims, $this->scrimInProgress);
    }

    #[Computed]
    public function receivedScrimRequests()
    {
        return ScrimRequest::with('requesterTeam')
            ->where('receiver_team_id', currentTeam()->id)
            ->where('status', StatusScrimRequest::PENDING)
            ->latest()
            ->get();
    }

    #[Computed]
    public function sentScrimRequests()
    {
        return ScrimRequest::with('receiverTeam')
            ->where('requester_team_id', currentTeam()->id)
            ->where('status', StatusScrimRequest::PENDING)
            ->latest()
            ->get();
    }

    #[Computed]
    public function scrims(): Collection
    {
        return Scrim::query()
            ->with(['opponentTeam'])
            ->where('team_id', currentTeam()->id)
            ->where('status', StatusScrim::SCHEDULED)
            ->orderBy('scheduled_date')
            ->orderBy('scheduled_time')
            ->get();
    }

    #[Computed]
    public function scrimInProgress(): ?Scrim
    {
        return Scrim::query()
            ->with(['opponentTeam', 'team', 'scrimGames'])
            ->where('team_id', currentTeam()->id)
            ->where('status', StatusScrim::IN_PROGRESS)
            ->orderByDesc('scheduled_date')
            ->orderByDesc('scheduled_time')
            ->first();
    }

    public function deleteScrimRequest(int $scrimRequestId)
    {
        if (Gate::denies('delete', ScrimRequest::class)) {
            $this->dispatch('toast', [
                'title' => __('policies/scrim_request.error_title'),
                'message' => __('policies/scrim_request.error_message'),
                'type' => 'error',
            ]);
            return;
        }
        $this->dispatch('open_modal', [
            'form' => 'scrims.delete-scrim-request',
            'model_id' => $scrimRequestId,
        ]);
    }

    public function showScrimRequest(int $scrimRequestId)
    {
        $this->dispatch('open_modal', [
            'form' => 'scrims.show-scrim-request',
            'model_id' => $scrimRequestId,
        ]);
    }

    public function showScrim(int $scrimId): void
    {
        $this->dispatch('open_modal', [
            'form' => 'scrims.show-scrim',
            'model_id' => $scrimId,
        ]);
    }
};
?>

<div class="flex flex-col gap-8">
    @if ($this->scrimInProgress)
    @php
    $activeScrim = $this->scrimInProgress;
    $activeScrimWins = $activeScrim->scrimGames->where('is_victory', true)->count();
    $activeScrimLosses = $activeScrim->scrimGames->where('is_victory', false)->count();
    @endphp
    <section wire:poll.10s class="relative flex flex-col gap-8 bg-bg-widget p-6 shadow-basic md:p-8">
        <div class="absolute right-6 top-6 inline-flex rounded-full items-center gap-2 bg-bg-card px-3 py-1.5">
            <span class="size-2 shrink-0 rounded-full bg-green-500" aria-hidden="true"></span>
            <span class="text-sm font-semibold text-text-primary">{{ __('pages/scrims/index.in_progress_badge') }}</span>
        </div>

        <div class="grid grid-cols-1 items-center gap-8 pt-8 lg:grid-cols-3 lg:pt-0">
            <div class="flex flex-col items-center gap-4">
                <img
                    src="{{ $activeScrim->team->logo_url }}"
                    alt="{{ $activeScrim->team->name }}"
                    class="size-20 object-cover md:size-24" />
                <p class="text-center text-xl font-bold text-text-primary md:text-2xl">
                    {{ $activeScrim->team->name }}
                </p>
            </div>

            <div class="flex flex-col items-center gap-2">
                <p class="text-5xl font-bold tabular-nums text-text-primary md:text-6xl">
                    <span class="text-victory">{{ $activeScrimWins }}</span>
                    <span class="text-text-secondary"> - </span>
                    <span class="text-defeat">{{ $activeScrimLosses }}</span>
                </p>
                <p class="text-sm bg-bg-card px-3 py-1 rounded-full  font-semibold text-text-secondary">
                    BO{{ $activeScrim->number_of_games }}
                </p>
            </div>

            <div class="flex flex-col items-center gap-4">
                <img
                    src="{{ $activeScrim->opponentTeam?->logo_url }}"
                    alt="{{ $activeScrim->opponentTeam?->name ?? __('pages/scrims/index.upcoming_opponent_unknown') }}"
                    class="size-20 object-cover md:size-24" />
                <p class="text-center text-xl font-bold text-text-primary md:text-2xl">
                    {{ $activeScrim->opponentTeam?->name ?? __('pages/scrims/index.upcoming_opponent_unknown') }}
                </p>
            </div>
        </div>

        <div class="flex flex-col items-center justify-center gap-4 sm:flex-row">
            <x-cta
                wire:navigate
                :href="route('scrims.show', ['slug' => currentTeam()->slug, 'id' => $activeScrim->id])"
                :title="__('pages/scrims/index.show_scrim_title')"
                :class="'primary'">
                {{ __('pages/scrims/index.show_scrim') }}
            </x-cta>
            <x-cta
                :href="'#'"
                :title="__('pages/scrims/index.finish_scrim_title')"
                :class="'secondary'">
                {{ __('pages/scrims/index.finish_scrim') }}
            </x-cta>
        </div>
    </section>
    @endif

    <section class="flex flex-col gap-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-2xl font-bold">
                {{ __('pages/scrims/index.title') }}
            </h2>
            <x-cta :href="route('scrims.find', ['slug' => currentTeam()->slug])" :title="__('pages/scrims/index.create_scrim')" :class="'cta-primary'">
                {{ __('pages/scrims/index.create_scrim') }}
            </x-cta>
        </div>
        <div class="grid grid-cols-12 gap-6">
            <div class="col-span-12 lg:col-span-6">
                {{-- Scrims à venir --}}
                <x-accordion
                    :title="__('pages/scrims/index.upcoming_title')"
                    :open="true"
                    :count="$this->scrims->count()"
                    heading-level="h3">
                    @foreach ($this->scrims as $scrim)
                    @php
                    $opponent = $scrim->opponentTeam;
                    $scheduledAt = $scrim->scheduled_time;
                    $scheduledDate = $scrim->scheduled_date;
                    @endphp
                    <li class="col-span-12">
                        <article x-on:click="$el.querySelector('[data-scrim-link]')?.click()" class="relative cursor-pointer flex flex-col border-l-2 border-gold bg-bg-card p-4 basic-shadow md:p-5 card-animated-border">
                            <span class="card-animated-border-right-edge" aria-hidden="true"></span>
                            <div class="relative z-[1] flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <div class="flex min-w-0 flex-1 items-center gap-4 md:gap-6">
                                    <div class="flex w-[4.25rem] shrink-0 flex-col gap-1 items-center border-r border-white/10 md:w-[4.75rem]">
                                        <p class="text-xs font-semibold uppercase  text-white">{{ $scheduledAt->translatedFormat('M') }}</p>
                                        <p class="text-2xl font-bold text-white">{{ $scheduledDate->format('j') }}</p>
                                        <span class="h-0.5 w-8 shrink-0 bg-gold" aria-hidden="true"></span>
                                    </div>
                                    <div class="flex min-w-0 flex-1 items-center gap-3 md:gap-4">
                                        <img
                                            src="{{ $opponent->logo_url }}"
                                            alt="{{ $opponent->name }}"
                                            class="size-14 shrink-0 object-cover md:size-16" />
                                        <div class="flex flex-col gap-1">
                                            <h4 class="text-xl font-bold text-white">{{ $opponent->name }}</h4>
                                            <p class="text-sm text-text-secondary">
                                                {{ __('pages/scrims/index.upcoming_format_time', ['games' => $scrim->number_of_games, 'time' => $scheduledAt->format('H:i')]) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <x-cta wire:navigate data-scrim-link :href="route('scrims.show', ['slug' => currentTeam()->slug, 'id' => $scrim->id])" :class="'primary'" :title="__('pages/scrims/index.show_scrim_title')">
                                    {{ __('pages/scrims/index.show_scrim') }}
                                </x-cta>
                            </div>
                        </article>
                    </li>
                    @endforeach
                </x-accordion>
            </div>

            <div class="col-span-12 flex flex-col gap-6 lg:col-span-6 lg:gap-8">
                {{-- Demande de scrims reçus --}}
                <x-accordion
                    :title="__('pages/scrims/index.received_title')"
                    :open="true"
                    :count="$this->receivedScrimRequests->count()"
                    heading-level="h3">
                    @foreach ($this->receivedScrimRequests as $request)
                    @php
                    $otherTeam = $request->requesterTeam;
                    $scheduledAt = \Carbon\Carbon::parse($request->scheduled_date->format('Y-m-d').' '.$request->scheduled_time);
                    @endphp
                    <li class="col-span-12">
                        <article x-on:click="$el.querySelector('[data-scrim-request-link]')?.click()" class="relative cursor-pointer flex flex-col border-l-2 border-gold bg-bg-card p-4 basic-shadow md:p-5 card-animated-border">
                            <span class="card-animated-border-right-edge" aria-hidden="true"></span>
                            <div class="relative z-[1] flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <div class="flex min-w-0 flex-1 items-center gap-3 md:gap-4">
                                    <img
                                        src="{{ $otherTeam->logo_url }}"
                                        alt="{{ $otherTeam->name }}"
                                        class="size-14 shrink-0 rounded object-cover md:size-16" />
                                    <div class="min-w-0">
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
                                <div class="shrink-0 sm:self-center">
                                    <button data-scrim-request-link wire:click="showScrimRequest({{ $request->id }})" type="button" class="cta-primary block w-full whitespace-nowrap px-4 py-2 text-center text-sm sm:w-auto">
                                        {{ __('pages/scrims/index.show_request') }}
                                    </button>
                                </div>
                            </div>
                        </article>
                    </li>
                    @endforeach
                </x-accordion>

                {{-- Demande de scrims envoyés --}}
                <x-accordion
                    :title="__('pages/scrims/index.sent_title')"
                    :open="true"
                    :count="$this->sentScrimRequests->count()"
                    heading-level="h3">
                    @foreach ($this->sentScrimRequests as $request)
                    @php
                    $otherTeam = $request->receiverTeam;
                    $scheduledAt = \Carbon\Carbon::parse($request->scheduled_date->format('Y-m-d').' '.$request->scheduled_time);
                    @endphp
                    <li class="col-span-12">
                        <article class="relative flex flex-col bg-bg-card p-4 basic-shadow md:p-5">
                            <div class="relative z-[1] flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <div class="flex min-w-0 flex-1 items-center gap-3 md:gap-4">
                                    <img
                                        src="{{ $otherTeam->logo_url }}"
                                        alt="{{ $otherTeam->name }}"
                                        class="size-14 shrink-0 rounded object-cover md:size-16" />
                                    <div class="min-w-0">
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
                                @can('delete', ScrimRequest::class)
                                <div class="shrink-0 sm:self-center">
                                    <x-destructive wire:click="deleteScrimRequest({{ $request->id }})" type="button" class="cta-danger block w-full whitespace-nowrap px-4 py-2 text-center text-sm sm:w-auto">
                                        {{ __('pages/scrims/index.cancel_request') }}
                                    </x-destructive>
                                </div>
                                @endcan
                            </div>
                        </article>
                    </li>
                    @endforeach
                </x-accordion>
            </div>
        </div>
    </section>


</div>