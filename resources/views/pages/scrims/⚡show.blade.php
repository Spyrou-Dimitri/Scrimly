<?php

use App\Enums\TypeScrimGameNote;
use App\Models\Scrim;
use App\Models\Team;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use App\Enums\StatusScrim;
use Illuminate\Support\Facades\Gate;

new #[Layout('layouts::team')] class extends Component
{
    use WithPagination;

    public Scrim $scrim;

    public Team $opponentTeam;

    public function mount(int $id): void
    {
        $this->scrim = Scrim::query()
            ->whereKey($id)
            ->where('team_id', currentTeam()->id)
            ->with([
                'opponentTeam',
                'team',
            ])
            ->firstOrFail();
    }
    #[Computed]
    public function scrimGames()
    {
        return $this->scrim->scrimGames()
            ->with([
                'scrimGamePlayers.teamMember.user',
                'scrimGameNotes',
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(5);
    }

    public function handleCreateGame(): void
    {
        if ($this->scrimGames->count() >= $this->scrim->number_of_games) {
            $this->dispatch('open_modal', [
                'form' => 'modals::scrims.handle-create-game',
                'model_id' => $this->scrim->id,
            ]);
        } else {
            redirect()->route('scrims.games.create', ['slug' => $this->scrim->team->slug, 'id' => $this->scrim->id]);
        }
    }

    public function openModalCompleteScrim(): void
    {
        if (Gate::denies('manageTeam', User::class)) {
            $this->dispatch('toast', [
                'title' => __('policies/scrim.error_title'),
                'message' => __('policies/scrim.error_finish_scrim'),
                'type' => 'error',
            ]);
            return;
        }
        $this->dispatch('open_modal', [
            'form' => 'modals::scrims.finish-scrim',
            'model_id' => $this->scrim->id,
        ]);
    }

    public function openDeleteGameModal(int $gameId): void
    {
        if (Gate::denies('manageTeam', User::class)) {
            $this->dispatch('toast', [
                'title' => __('policies/scrim.error_title'),
                'message' => __('policies/scrim.error_delete_game'),
                'type' => 'error',
            ]);
            return;
        }
        $this->dispatch('open_modal', [
            'form' => 'modals::scrims.games.delete-game',
            'model_id' => $gameId,
        ]);
    }
    public function openModalStartScrim(): void
    {
        if (Gate::denies('manageTeam', User::class)) {
            $this->dispatch('toast', [
                'title' => __('policies/scrim.error_title'),
                'message' => __('policies/scrim.error_start_scrim'),
                'type' => 'error',
            ]);
            return;
        }
        $this->dispatch('open_modal', [
            'form' => 'modals::scrims.start-scrim',
            'model_id' => $this->scrim->id,
        ]);
    }

    public function openModalCancelScrim(): void
    {
        if (Gate::denies('manageTeam', User::class)) {
            $this->dispatch('toast', [
                'title' => __('policies/scrim.error_title'),
                'message' => __('policies/scrim.error_cancel_scrim'),
                'type' => 'error',
            ]);

            return;
        }

        $this->dispatch('open_modal', [
            'form' => 'modals::scrims.cancel-scrim',
            'model_id' => $this->scrim->id,
        ]);
    }
    public function openModalDeleteScrim(): void
    {
        if (Gate::denies('manageTeam', User::class)) {
            $this->dispatch('toast', [
                'title' => __('policies/scrim.error_title'),
                'message' => __('policies/scrim.error_delete_scrim'),
                'type' => 'error',
            ]);
            return;
        }
        $this->dispatch('open_modal', [
            'form' => 'modals::scrims.delete-scrim',
            'model_id' => $this->scrim->id,
        ]);
    }

    public function openModalEditSummary(): void
    {
        if (Gate::denies('manageTeam', User::class)) {
            $this->dispatch('toast', [
                'title' => __('policies/scrim.error_title'),
                'message' => __('policies/scrim.error_edit_summary'),
                'type' => 'error',
            ]);

            return;
        }

        if ($this->scrim->status === StatusScrim::SCHEDULED) {
            $this->dispatch('toast', [
                'title' => __('policies/scrim.error_title'),
                'message' => __('modals/scrims/edit-summary.error_scheduled'),
                'type' => 'error',
            ]);

            return;
        }

        $this->dispatch('open_modal', [
            'form' => 'modals::scrims.edit-summary',
            'model_id' => $this->scrim->id,
        ]);
    }

    #[On('refresh_scrim')]
    public function refreshScrim(): void
    {
        $this->scrim->refresh();
        $this->scrim->load([
            'opponentTeam',
            'team',
            'scrimGames.scrimGamePlayers.teamMember.user',
            'scrimGames.scrimGameNotes',
        ]);
    }
};


?>

<div class="flex flex-col gap-10">
    @php
    $ddragonVersion = config('riot.ddragon_version');
    @endphp
    <section class="flex flex-col gap-8">
        <div class="flex flex-row flex-wrap items-center justify-between gap-4">
            <h2 class="text-[32px] font-bold">
                <span class="text-text-primary">{{ __('pages/scrims/show.title_prefix') }}</span>
                <span class="text-gold">{{ $this->scrim->opponentTeam?->name ?? __('pages/scrims/show.opponent_unknown') }}</span>
            </h2>
            @if ($this->scrim->status === StatusScrim::IN_PROGRESS)
            @can('manageTeam', User::class)
            <button wire:click="openModalCompleteScrim()" title="{{ __('pages/scrims/show.mark_as_completed') }}" class="cta-primary">
                {{ __('pages/scrims/show.mark_as_completed') }}
            </button>
            @endcan
            @elseif ($this->scrim->status === StatusScrim::SCHEDULED)
            @can('manageTeam', User::class)
            <div class="flex flex-row flex-wrap items-center gap-4">
                <button wire:click="openModalStartScrim()" title="{{ __('pages/scrims/show.start_scrim') }}" class="cta-primary">
                    {{ __('pages/scrims/show.start_scrim') }}
                </button>
                <x-destructive wire:click="openModalCancelScrim()" title="{{ __('pages/scrims/show.cancel_scrim_title') }}">
                    {{ __('pages/scrims/show.cancel_scrim') }}
                </x-destructive>
            </div>
            @endcan
            @elseif ($this->scrim->status === StatusScrim::COMPLETED || $this->scrim->status === StatusScrim::ABORTED)
            @can('manageTeam', User::class)
            <x-destructive wire:click="openModalDeleteScrim()" title="{{ __('pages/scrims/show.delete_scrim_title') }}">
                {{ __('pages/scrims/show.delete_scrim') }}
            </x-destructive>
            @endcan
            @endif
        </div>

        @php
        $gamesPlayed = $this->scrim->scrimGames->count();
        $gamesPlanned = $this->scrim->number_of_games;
        $opponentId = $this->scrim->opponent_team_id;
        @endphp

        <div
            class="grid w-full grid-cols-2 md:grid-cols-8  items-start gap-6 lg:grid-cols-12 ">
            <div class="order-1 col-span-1 md:col-span-2 lg:col-span-3 flex flex-col bg-bg-widget p-6 shadow-basic lg:order-1 ">
                <p class="text-sm text-gold font-bold">
                    {{ __('pages/scrims/show.widget_date') }}
                </p>
                <p class="mt-1 text-xl font-bold">
                    {{ $this->scrim->scheduled_date->locale(app()->getLocale())->translatedFormat('j M Y') }}
                </p>
            </div>

            <div class="order-2 col-span-1 md:col-span-2 lg:col-span-3 flex flex-col bg-bg-widget p-6 shadow-basic lg:order-2 ">
                <p class="text-sm text-gold font-bold">
                    {{ __('pages/scrims/show.widget_time') }}
                </p>
                <p class="mt-1 text-xl font-bold">
                    {{ $this->scrim->scheduled_at->format('H:i') }}
                </p>
            </div>

            <div class="order-5 col-span-2 md:col-span-full lg:col-span-6 flex min-h-[11rem] flex-col bg-bg-widget p-6 shadow-basic lg:order-3 lg:row-span-2">
                <div class="flex flex-row flex-wrap items-center justify-between gap-4">
                    <p class="text-sm text-gold font-bold">
                        {{ __('pages/scrims/show.widget_summary') }}
                    </p>
                    @can('manageTeam', User::class)
                        @if ($this->scrim->status !== StatusScrim::SCHEDULED)
                            <button
                                wire:click="openModalEditSummary"
                                type="button"
                                title="{{ __('pages/scrims/show.edit_summary_title') }}"
                                class="cta-secondary">
                                {{ __('pages/scrims/show.edit_summary') }}
                            </button>
                        @endif
                    @endcan
                </div>

                @if (filled($this->scrim->summary))
                <p class="mt-3 text-base text-text-primary">
                    {{ $this->scrim->summary }}
                </p>
                @else
                <p class="mt-3 text-base text-text-secondary">
                    {{ __('pages/scrims/show.summary_empty') }}
                </p>
                @endif
            </div>

            <div class="order-3 col-span-1 md:col-span-2 lg:col-span-3 flex flex-col bg-bg-widget p-6 shadow-basic lg:order-4 ">
                <p class="text-sm text-gold font-bold">
                    {{ __('pages/scrims/show.widget_game_count') }}
                </p>
                <p class="mt-1 text-xl font-bold tabular-nums">
                    {{ $gamesPlayed }}/{{ $gamesPlanned }}
                </p>
            </div>

            <div class="order-4 col-span-1 md:col-span-2 lg:col-span-3 flex flex-col bg-bg-widget p-6 shadow-basic lg:order-5 ">
                <p class="text-sm text-gold">
                    {{ __('pages/scrims/show.widget_results') }}
                </p>
                <p class="mt-1 text-xl font-bold tabular-nums">
                    <span class="text-victory">{{ $this->scrimGames->where('is_victory', true)->count() }}</span>
                    <span class="text-text-secondary"> - </span>
                    <span class="text-defeat">{{ $this->scrimGames->where('is_victory', false)->count() }}</span>
                </p>
            </div>
        </div>
    </section>
    <section class="flex flex-col gap-8">
        <div class="flex flex-row flex-wrap items-center justify-between gap-4">
            <h2 class="text-[32px] font-bold">
                {{ __('pages/scrims/show.games_title') }} <span class="text-gold font-bold">({{ $this->scrimGames->count() }})</span>
            </h2>
            @can('manageTeam', User::class)
            <button wire:click="handleCreateGame()" title="{{ __('pages/scrims/show.create_game_title') }}" class="cta-primary">
                {{ __('pages/scrims/show.create_game') }}
            </button>
            @endcan
        </div>
        @if ($this->scrim->scrimGames->isNotEmpty())
        <div class="flex flex-col gap-4">
            @foreach ($this->scrimGames as $game)
            <x-accordion
                wire:key="scrim-game-{{ $game->id }}"
                :open="false"
                panel-tag="div"
                panel-class="mt-6 flex flex-col gap-8">
                <x-slot:header>
                    <div class="flex flex-row flex-wrap items-end gap-6 gap-y-4 lg:gap-10">
                        <div class="flex flex-col gap-1">
                            <p class="text-sm text-text-secondary">{{ __('pages/scrims/show.game_header_label') }}</p>
                            <h3 class="text-2xl text-gold font-bold">{{ $game->title }}</h3>
                        </div>
                        <div class="flex flex-col gap-1">
                            <p class="text-sm text-text-secondary">{{ __('pages/scrims/show.result_header_label') }}</p>
                            @if ($game->is_victory)
                            <span class="inline-flex w-fit bg-victory/15 px-3 py-1 text-sm font-bold text-victory">
                                {{ __('pages/scrims/show.victory') }}
                            </span>
                            @else
                            <span class="inline-flex w-fit bg-defeat/15 px-3 py-1 text-sm font-bold text-defeat">
                                {{ __('pages/scrims/show.defeat') }}
                            </span>
                            @endif
                        </div>
                        <div class="flex flex-col gap-1">
                            <p class="text-sm text-text-secondary">{{ __('pages/scrims/show.duration_header_label') }}</p>
                            <p class="text-xl font-bold text-text-primary">{{ $game->formatted_duration }}</p>
                        </div>
                    </div>
                </x-slot:header>

                @can('manageTeam', User::class)
                <x-slot:actions>
                    <div class="flex flex-row items-center gap-4 pr-4 border-r border-white/10">
                        <a
                            title="{{ __('pages/scrims/show.edit_game_title') }}"
                            href="{{ route('scrims.games.edit', ['slug' => $this->scrim->team->slug, 'id' => $this->scrim->id, 'gameId' => $game->id]) }}"
                            class="group cursor-pointer flex size-8 items-center justify-center border border-transparent bg-bg-card text-text-primary transition-all duration-150 ease-in-out hover:border-gold">
                            <flux:icon name="pencil" class="size-5 transition-all duration-150 ease-in-out group-hover:text-gold" />
                        </a>
                        <button
                            type="button"
                            wire:click="openDeleteGameModal({{ $game->id }})"
                            title="{{ __('pages/scrims/show.delete_game') }}"
                            class="group cursor-pointer flex size-8 items-center justify-center border border-transparent bg-bg-card text-text-primary transition-all duration-150 ease-in-out hover:border-red-700/90">
                            <flux:icon name="trash" class="size-5 transition-all duration-150 ease-in-out group-hover:text-red-700/90" />
                        </button>
                    </div>
                </x-slot:actions>
                @endcan
                <section class="flex flex-col gap-4 border-t border-gold pt-6">
                    <h4 class="sr-only">{{ __('pages/scrims/show.score_section_title') }}</h4>
                    <div class="grid grid-cols-1 items-center gap-4 lg:grid-cols-3">
                        <div class="flex flex-row items-center gap-4">
                            <x-team-logo
                                :team="$this->scrim->team"
                                preset="scrim-row"
                                class="size-15" />
                            <p class="text-center text-2xl font-bold text-text-primary lg:text-left">
                                {{ $this->scrim->team->name }}
                            </p>
                        </div>

                        <div class="flex flex-col items-center gap-3">
                            <p class="text-5xl font-bold text-text-primary">
                                <span @class(['text-victory'=> $game->is_victory, 'text-defeat' => !$game->is_victory])>
                                    {{ $game->homeTeamTotalKills() }}
                                </span> :
                                <span @class(['text-victory'=> !$game->is_victory, 'text-defeat' => $game->is_victory])>
                                    {{ $game->opponentTeamTotalKills() }}
                                </span>
                            </p>
                            <span class="rounded-full bg-bg-card px-4 py-1 text-sm text-text-secondary">
                                {{ __('pages/scrims/show.face_to_face') }}
                            </span>
                        </div>
                        <div class="flex flex-row justify-end items-center gap-4">
                            <p class="text-center text-2xl font-bold text-text-primary lg:text-right">
                                {{ $this->scrim->opponentTeam?->name }}
                            </p>
                            @if ($this->scrim->opponentTeam)
                            <x-team-logo
                                :team="$this->scrim->opponentTeam"
                                preset="scrim-row"
                                class="size-15" />
                            @endif

                        </div>
                    </div>
                </section>

                <section>
                    <h4 class="sr-only">{{ __('pages/scrims/show.game_draft_title') }}</h4>
                    <div class="grid grid-cols-1 gap-8 lg:grid-cols-2 lg:gap-10">
                        <ul class="flex flex-col gap-4">
                            @foreach ($game->scrimGamePlayers as $player)
                            <li class="flex flex-row flex-wrap items-center justify-between gap-4 bg-bg-card p-6 shadow-basic">
                                <div class="flex flex-row items-center gap-3">
                                    <img src="https://ddragon.leagueoflegends.com/cdn/{{ $ddragonVersion }}/img/champion/{{ $player->champion }}.png" alt="{{ $player->champion }}" class="size-15">
                                    <div class="flex flex-col gap-3">
                                        <h5 class="text-lg font-bold leading-none">
                                            {{ $player->teamMember->user->username }}
                                        </h5>
                                        <p @class(['text-sm font-bold', 'text-victory'=> $game->is_victory, 'text-defeat' => !$game->is_victory])>
                                            {{ $player->champion }} • {{ $player->teamMember->roleInGame->label() }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex flex-col gap-3 text-right">
                                    <p class="font-bold leading-none">
                                        {{ $player->kills }} / {{ $player->deaths }} / {{ $player->assists }}
                                    </p>
                                    <p @class(['text-sm font-bold', 'text-victory'=> $game->is_victory, 'text-defeat' => !$game->is_victory])>
                                        K/D/A : {{ $player->general_kda }}
                                    </p>
                                </div>

                            </li>
                            @endforeach

                        </ul>
                        <ul class="flex flex-col gap-4">
                            @foreach (['top', 'jungle', 'mid', 'bot', 'support'] as $role)
                            @php
                            $opponentPlayer = $game->opponent_team_members_starters[$role] ?? null;
                            @endphp
                            @if ($opponentPlayer)
                            <li class="flex flex-row flex-wrap items-center justify-between gap-4 bg-bg-card p-6 shadow-basic">
                                <div class="flex flex-row items-center gap-3">
                                    <img src="https://ddragon.leagueoflegends.com/cdn/{{ $ddragonVersion }}/img/champion/{{ $opponentPlayer['champion'] }}.png" alt="{{ $opponentPlayer['champion'] }}" class="size-15">
                                    <div class="flex flex-col gap-3">
                                        <h5 class="text-lg font-bold leading-none">
                                            {{ __('pages/scrims/games/create.role_'.$role) }}
                                        </h5>
                                        <p @class(['text-sm font-bold', 'text-victory'=> ! $game->is_victory, 'text-defeat' => $game->is_victory])>
                                            {{ $opponentPlayer['champion'] }} • {{ __('pages/scrims/games/create.role_'.$role) }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex flex-col gap-3 text-right">
                                    <p class="font-bold leading-none">
                                        {{ $opponentPlayer['kills'] }} / {{ $opponentPlayer['deaths'] }} / {{ $opponentPlayer['assists'] }}
                                    </p>
                                    <p @class(['text-sm font-bold', 'text-victory'=> ! $game->is_victory, 'text-defeat' => $game->is_victory])>
                                        K/D/A : {{ $game->calculateKda($opponentPlayer['kills'], $opponentPlayer['deaths'], $opponentPlayer['assists']) }}
                                    </p>
                                </div>
                            </li>
                            @endif
                            @endforeach
                        </ul>
                    </div>
                </section>

                <section class="flex flex-col gap-6">
                    <h4 class="sr-only">{{ __('pages/scrims/show.notes_section_title') }}</h4>

                    @php
                    $positiveNotes = $game->scrimGameNotes->where('type', TypeScrimGameNote::POSITIVE);
                    $negativeNotes = $game->scrimGameNotes->where('type', TypeScrimGameNote::NEGATIVE);
                    @endphp

                    <div class="grid grid-cols-1 gap-8 lg:grid-cols-2 pt-6 border-t border-gold lg:gap-10">
                        <div class="flex flex-col gap-4">
                            <div class="flex items-center gap-3">
                                <span class="h-6 w-1 shrink-0 bg-green-500" aria-hidden="true"></span>
                                <h5 class="text-2xl font-bold text-white">
                                    {{ __('pages/scrims/show.positive_notes_title') }}
                                </h5>
                                <span class="inline-flex min-w-8 items-center justify-center bg-bg-card px-2 py-0.5 font-semibold text-green-500">
                                    {{ $positiveNotes->count() }}
                                </span>
                            </div>

                            @if ($positiveNotes->isNotEmpty())
                            <ul class="flex flex-col gap-2" role="list">
                                @foreach ($positiveNotes as $note)
                                <li wire:key="scrim-game-{{ $game->id }}-note-positive-{{ $note->id }}" class="bg-bg-card p-4">
                                    <span class="text-white">{{ $note->note }}</span>
                                </li>
                                @endforeach
                            </ul>
                            @else
                            <p class="text-text-secondary">{{ __('pages/scrims/show.no_positive_notes') }}</p>
                            @endif
                        </div>

                        <div class="flex flex-col gap-4">
                            <div class="flex items-center gap-3">
                                <span class="h-6 w-1 shrink-0 bg-red-500" aria-hidden="true"></span>
                                <h5 class="text-2xl font-bold text-white">
                                    {{ __('pages/scrims/show.negative_notes_title') }}
                                </h5>
                                <span class="inline-flex min-w-8 items-center justify-center bg-bg-card px-2 py-0.5 font-semibold text-red-500">
                                    {{ $negativeNotes->count() }}
                                </span>
                            </div>

                            @if ($negativeNotes->isNotEmpty())
                            <ul class="flex flex-col gap-2" role="list">
                                @foreach ($negativeNotes as $note)
                                <li wire:key="scrim-game-{{ $game->id }}-note-negative-{{ $note->id }}" class="bg-bg-card p-4">
                                    <span class="text-white">{{ $note->note }}</span>
                                </li>
                                @endforeach
                            </ul>
                            @else
                            <p class="text-text-secondary">{{ __('pages/scrims/show.no_negative_notes') }}</p>
                            @endif
                        </div>
                    </div>
                </section>
            </x-accordion>
            @endforeach
            {{ $this->scrimGames->links() }}
        </div>
        @else
        <div class="flex flex-col gap-4">
            <p class="text-text-secondary">{{ __('pages/scrims/show.no_games') }}</p>
        </div>
        @endif
    </section>
</div>