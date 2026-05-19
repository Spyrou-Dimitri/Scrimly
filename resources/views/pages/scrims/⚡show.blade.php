<?php

use App\Models\Scrim;
use App\Models\Team;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::team')] class extends Component
{
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
                'scrimGames.scrimGamePlayers.teamMember.user',
            ])
            ->firstOrFail();

        $this->opponentTeam = Team::find($this->scrim->opponent_team_id);
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
            <x-cta :href="'#'" :title="__('pages/scrims/index.create_scrim')" :class="'cta-primary'">
                {{ __('pages/scrims/show.mark_as_completed') }}
            </x-cta>
        </div>

        @php
        $resolvedSummary = filled(trim(($this->scrim->summary ?? ''))) ? $this->scrim->summary : null;
        $gamesPlayed = $this->scrim->scrimGames->count();
        $gamesPlanned = $this->scrim->number_of_games;
        $opponentId = $this->scrim->opponent_team_id;
        @endphp

        <div
            class="grid w-full grid-cols-2 md:grid-cols-8  gap-6 lg:grid-cols-12 ">
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
                    {{$this->scrim->scheduled_time->format('H:i')}}
                </p>
            </div>

            <div class="order-5 col-span-2 md:col-span-full lg:col-span-6 flex min-h-[11rem] flex-col bg-bg-widget p-6 shadow-basic lg:order-3 lg:row-span-2">
                <p class="text-sm text-gold font-bold">
                    {{ __('pages/scrims/show.widget_summary') }}
                </p>
                @if ($resolvedSummary !== null)
                <p class="mt-3 text-base text-text-primary">
                    {{ $resolvedSummary }}
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
                    <span class="text-victory">0</span>
                    <span class="text-text-secondary"> - </span>
                    <span class="text-defeat">0</span>
                </p>
            </div>
        </div>
    </section>
    <section class="flex flex-col gap-8">
        <div class="flex flex-row flex-wrap items-center justify-between gap-4">
            <h2 class="text-[32px] font-bold">
                {{ __('pages/scrims/show.games_title') }}
            </h2>
            <x-cta wire:navigate :href="route('scrims.games.create', ['slug' => $this->scrim->team->slug, 'id' => $this->scrim->id])" :title="__('pages/scrims/show.create_game_title')" :class="'cta-primary'">
                {{ __('pages/scrims/show.create_game') }}
            </x-cta>
        </div>
        <div class="flex flex-col gap-4">
            @foreach ($this->scrim->scrimGames as $game)
            <article class="flex bg-bg-widget flex-col gap-4 p-6">
                <div class="flex flex-row items-center justify-between gap-4">
                    <div class=" flex flex-row items-center gap-10">
                        <h3 class="text-2xl text-gold font-bold"> {{ $game->title}}</h3>
                        @if ($game->is_victory)
                        <p class="text-2xl text-victory">
                            {{ __('pages/scrims/show.victory') }}
                        </p>
                        @else
                        <p class="text-2xl text-defeat font-bold">
                            {{ __('pages/scrims/show.defeat') }}
                        </p>
                        @endif
                        <p class="text-base text-text-secondary">
                            {{ $game->formatted_duration }} {{ __('pages/scrims/show.duration_label') }}
                        </p>
                    </div>
                    <flux:icon name="chevron-right" class="size-6 shrink-0 text-text-secondary" />
                </div>
                <div class="flex flex-col gap-4">
                    <div class="flex flex-row items-center justify-between gap-2 pb-6 border-b border-gold">
                        <h4 class="text-xl font-bold">{{ __('pages/scrims/show.game_draft_title') }}</h4>
                        <button type="button" class="cta-primary">
                            {{ __('pages/scrims/show.edit_game') }}
                        </button>   
                    </div>
                    <div class="grid grid-cols-1 gap-8 lg:grid-cols-2 lg:gap-10">
                        <div class="flex flex-col gap-5">
                            <h5 class="text-lg font-bold">
                                {{ $this->scrim->team->name }}
                            </h5>
                            <ul class="flex flex-col gap-4">
                                @foreach ($game->scrimGamePlayers as $player)
                                <li class="flex flex-row flex-wrap items-center justify-between gap-4 bg-bg-card p-6 shadow-basic">
                                    <div class="flex flex-row items-center gap-3">
                                        <img src="https://ddragon.leagueoflegends.com/cdn/{{ $ddragonVersion }}/img/champion/{{ $player->champion }}.png" alt="{{ $player->champion }}" class="size-15">
                                        <div class="flex flex-col gap-3">
                                            <h6 class="text-lg font-bold leading-none">
                                                {{ $player->teamMember->user->username }}
                                            </h6>
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
                        </div>

                        <div class="flex flex-col gap-5">
                            <h5 class="text-lg font-bold">
                                {{ $this->opponentTeam->name }}
                            </h5>
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
                                            <h6 class="text-lg font-bold leading-none">
                                                {{ __('pages/scrims/games/create.role_'.$role) }}
                                            </h6>
                                            <p @class(['text-sm font-bold', 'text-victory' => ! $game->is_victory, 'text-defeat' => $game->is_victory])>
                                                {{ $opponentPlayer['champion'] }} • {{ __('pages/scrims/games/create.role_'.$role) }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex flex-col gap-3 text-right">
                                        <p class="font-bold leading-none">
                                            {{ $opponentPlayer['kills'] }} / {{ $opponentPlayer['deaths'] }} / {{ $opponentPlayer['assists'] }}
                                        </p>
                                        <p @class(['text-sm font-bold', 'text-victory' => ! $game->is_victory, 'text-defeat' => $game->is_victory])>
                                            K/D/A : {{ $game->calculateKda($opponentPlayer['kills'], $opponentPlayer['deaths'], $opponentPlayer['assists']) }}
                                        </p>
                                    </div>
                                </li>
                                @endif
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </article>
            @endforeach
        </div>
    </section>
</div>
