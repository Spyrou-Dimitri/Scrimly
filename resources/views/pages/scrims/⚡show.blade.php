<?php

use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\Scrim;

new #[Layout('layouts::team')] class extends Component {
    public Scrim $scrim;

    public function mount(int $id): void
    {
        $this->scrim = Scrim::query()
            ->whereKey($id)
            ->where('team_id', currentTeam()->id)
            ->with([
                'opponentTeam',
                'scrimGames',
            ])
            ->firstOrFail();
    }
};


?>

<div class="flex flex-col gap-10">
    <section class="flex flex-col gap-8">
        <div class="flex flex-row flex-wrap items-center justify-between gap-4">
            <h2 class="text-2xl font-bold">
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
            <h2 class="text-2xl font-bold">
                {{ __('pages/scrims/show.games_title') }}
            </h2>
            <x-cta wire:navigate :href="route('scrims.games.create', ['slug' => $this->scrim->team->slug, 'id' => $this->scrim->id])" :title="__('pages/scrims/show.create_game_title')" :class="'cta-primary'">
                {{ __('pages/scrims/show.create_game') }}
            </x-cta>
        </div>
    </section>
</div>