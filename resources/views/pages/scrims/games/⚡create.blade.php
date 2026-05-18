<?php

use App\Models\Scrim;
use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\TeamMember;
use Illuminate\Support\Collection;

new #[Layout('layouts::team')] class extends Component
{
    public Scrim $scrim;

    public string $gameName = 'Game 1';

    public string $durationMinutes = '20';

    public string $durationSeconds = '53';

    public ?string $outcome = null;

    public string $side_of_your_team = 'blue_side';

    public Collection $teamMembersStarters;

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

        $this->teamMembersStarters = TeamMember::query()
            ->where('team_id', currentTeam()->id)
            ->where('is_starter', true)
            ->orderBy('roleInGame')
            ->with('user')
            ->get();
    }
};
?>

@php
$teamsSummary =
$this->scrim->team->name.
' vs '.
($this->scrim->opponentTeam?->name ?? __('pages/scrims/show.opponent_unknown'));

$champions = collect(getChampionsList())->sortBy('name')->values()->all();

$draftRows = [
['key' => 'top', 'role' => __('pages/scrims/games/create.role_top')],
['key' => 'jungle', 'role' => __('pages/scrims/games/create.role_jungle')],
['key' => 'mid', 'role' => __('pages/scrims/games/create.role_mid')],
['key' => 'bot', 'role' => __('pages/scrims/games/create.role_bot')],
['key' => 'support', 'role' => __('pages/scrims/games/create.role_support')],
];
@endphp

<section class="flex flex-col gap-8">
    <div class="flex flex-row flex-wrap items-center justify-between gap-4">
        <div class="flex flex-col gap-2">
            <h2 class="text-2xl font-bold text-text-primary">
                {{ __('pages/scrims/games/create.page_title') }}
            </h2>
            <p class="text-base text-text-secondary">
                {{ __('pages/scrims/games/create.scrim_label', ['teams' => $teamsSummary]) }}
            </p>
        </div>
        <button type="button" class="cta-primary shrink-0" title="{{ __('pages/scrims/games/create.create_button') }}">
            {{ __('pages/scrims/games/create.create_button') }}
        </button>
    </div>
    <form action="" class="grid grid-cols-12 gap-6">
        <fieldset class="min-w-0 flex flex-col gap-6 border-0 bg-bg-widget p-6 shadow-basic col-span-full">
            <legend class="sr-only">
                {{ __('pages/scrims/games/create.main_fieldset_legend') }}
            </legend>
            <h3 class="text-2xl font-bold text-gold pb-4 border-b border-gold">
                {{ __('pages/scrims/games/create.main_fieldset_legend') }}
            </h3>


            <div class="grid grid-cols-1 gap-8 lg:grid-cols-3 lg:gap-6">

                {{-- Name --}}
                <div class="flex w-full flex-col gap-2">
                    <span class="block font-medium text-white">
                        {{ __('pages/scrims/games/create.name_label') }}
                    </span>
                    <x-forms.input
                        :srOnlyLabel="true"
                        wire:model.live="gameName"
                        :required="true"
                        class="w-full"
                        :label="__('pages/scrims/games/create.name_label')"
                        :name="'game-name'"
                        :type="'text'" />
                </div>

                {{-- Duration --}}
                <div class="flex w-full flex-col gap-2">
                    <span class="block font-medium text-white">
                        {{ __('pages/scrims/games/create.duration_label') }}
                    </span>
                    <div class="flex items-center gap-4">
                        <div class="flex flex-1 items-center gap-2">
                            <x-forms.input
                                :srOnlyLabel="true"
                                wire:model.live="durationMinutes"
                                :required="false"
                                min="0"
                                max="120"
                                class="w-full"
                                :label="__('pages/scrims/games/create.duration_minutes_aria')"
                                :name="'game-duration-minutes'"
                                :type="'number'" />
                            <span class="text-text-secondary">{{ __('pages/scrims/games/create.minutes_suffix') }}</span>
                        </div>
                        <div class="flex flex-1 items-center gap-2">
                            <x-forms.input
                                :srOnlyLabel="true"
                                wire:model.live="durationSeconds"
                                :required="false"
                                min="0"
                                max="59"
                                class="w-full"
                                :label="__('pages/scrims/games/create.duration_seconds_aria')"
                                :name="'game-duration-seconds'"
                                :type="'number'" />
                            <span class="text-text-secondary">{{ __('pages/scrims/games/create.seconds_suffix') }}</span>
                        </div>
                    </div>
                </div>

                {{-- Result --}}
                <div class="flex flex-col gap-3">
                    <x-forms.radio
                        variant="outcome"
                        name="game_outcome"
                        :columns="2"
                        :required="true"
                        :label="__('pages/scrims/games/create.result_label')"
                        :options="[
                            [
                                'value' => 'win',
                                'label' => __('pages/scrims/games/create.result_win'),
                            ],
                            [
                                'value' => 'loss',
                                'label' => __('pages/scrims/games/create.result_loss'),
                            ],
                        ]"
                        grid-gap-class="gap-3 sm:gap-4"
                        wire:model.live="outcome" />
                </div>
            </div>
        </fieldset>

        <div class="col-span-full grid grid-cols-1 gap-8 lg:grid-cols-2 lg:gap-10">
            <fieldset class="min-w-0 flex flex-col gap-6 border-0 bg-bg-widget p-6 shadow-basic">
                <legend class="sr-only">
                    {{ __('pages/scrims/games/create.draft_home_fieldset_legend') }}
                </legend>
                <h3 class="border-b border-gold pb-4 text-2xl font-bold text-gold">
                    {{ $this->scrim->team->name }}
                    <span class="font-semibold text-gold">— {{ __('pages/scrims/games/create.score_draft_legend') }}</span>
                </h3>

                <div class="flex flex-col gap-4">
                    @foreach ($this->teamMembersStarters as $teamMember)
                    <article class="flex flex-col gap-4 bg-bg-card p-4 shadow-basic">

                        <div class="flex flex-wrap items-center gap-2">
                            <div class="flex items-center gap-2">
                                <img src="{{ $teamMember->user->avatar_url }}" alt="{{ $teamMember->user->username }}" class="w-10 h-10">
                                <h4 class="text-xl font-semibold text-gold">
                                    {{ $teamMember->user->username }} - {{ $teamMember->roleInGame->label() }}
                                </h4>
                            </div>

                        </div>

                        <x-forms.select
                            :hasLabel="true"
                            :required="false"
                            :name="$teamMember->user->username.'_champion_id'"
                            :label="__('pages/scrims/games/create.champion_label')"
                            :disabled="__('pages/scrims/games/create.champion_select_placeholder')"
                            :options="$champions" />

                        <div class="flex flex-col gap-2">
                            <span class="block font-medium text-white">
                                {{ __('pages/scrims/games/create.score_label') }}
                            </span>
                            <div class="flex items-center justify-center gap-2 sm:justify-start">
                                <div class="flex-1">
                                    <x-forms.input
                                        :srOnlyLabel="true"
                                        :required="false"
                                        min="0"
                                        class="text-center px-2"
                                        :label="__('pages/scrims/games/create.kda_kill_placeholder')"
                                        :name="$teamMember->user->username.'_kills'"
                                        :type="'number'"
                                        :placeholder="__('pages/scrims/games/create.kda_kill_placeholder')" />
                                </div>
                                <span class="text-text-secondary" aria-hidden="true">/</span>
                                <div class="flex-1">
                                    <x-forms.input
                                        :srOnlyLabel="true"
                                        :required="false"
                                        min="0"
                                        class="text-center px-2"
                                        :label="__('pages/scrims/games/create.kda_death_placeholder')"
                                        :name="$teamMember->user->username.'_deaths'"
                                        :type="'number'"
                                        :placeholder="__('pages/scrims/games/create.kda_death_placeholder')" />
                                </div>
                                <span class="text-text-secondary" aria-hidden="true">/</span>
                                <div class="flex-1">
                                    <x-forms.input
                                        :srOnlyLabel="true"
                                        :required="false"
                                        min="0"
                                        class="text-center px-2"
                                        :label="__('pages/scrims/games/create.kda_assist_placeholder')"
                                        :name="$teamMember->user->username.'_assists'"
                                        :type="'number'"
                                        :placeholder="__('pages/scrims/games/create.kda_assist_placeholder')" />
                                </div>
                            </div>
                        </div>
                    </article>
                    @endforeach
                </div>
            </fieldset>

            {{-- Line-up adversaire --}}
            <fieldset class="min-w-0 flex flex-col gap-6 border-0 bg-bg-widget p-6 shadow-basic">
                <legend class="sr-only">
                    {{ __('pages/scrims/games/create.draft_away_fieldset_legend') }}
                </legend>
                <h3 class="border-b border-gold pb-4 text-2xl font-bold text-gold">
                    {{ $this->scrim->opponentTeam?->name ?? __('pages/scrims/show.opponent_unknown') }}
                    <span class="font-semibold text-gold">— {{ __('pages/scrims/games/create.score_draft_legend') }}</span>
                </h3>

                <div class="flex flex-col gap-4">
                    @foreach ($draftRows as $row)
                    <article class="flex flex-col gap-4 bg-bg-card p-4 shadow-basic">
                        <div class="flex flex-wrap items-center gap-2">
                            <div class="flex items-center gap-2">
                                <h4 class="text-xl font-semibold text-gold">
                                    {{ __('pages/scrims/games/create.opponent_lineup_name') }}
                                    -
                                    {{ $row['role'] }}
                                </h4>
                            </div>
                        </div>

                        <x-forms.select
                            :hasLabel="true"
                            :required="false"
                            :name="'draft_away_'.$row['key'].'_champion_id'"
                            :label="__('pages/scrims/games/create.champion_label')"
                            :disabled="__('pages/scrims/games/create.champion_select_placeholder')"
                            :options="$champions" />

                        <div class="flex flex-col gap-2">
                            <span class="block font-medium text-white">
                                {{ __('pages/scrims/games/create.score_label') }}
                            </span>
                            <div class="flex items-center justify-center gap-2 sm:justify-start">
                                <div class="flex-1">
                                    <x-forms.input
                                        :srOnlyLabel="true"
                                        :required="false"
                                        min="0"
                                        class="text-center px-2"
                                        :label="__('pages/scrims/games/create.kda_kill_placeholder')"
                                        :name="'draft_away_'.$row['key'].'_kills'"
                                        :type="'number'"
                                        :placeholder="__('pages/scrims/games/create.kda_kill_placeholder')" />
                                </div>
                                <span class="text-text-secondary" aria-hidden="true">/</span>
                                <div class="flex-1">
                                    <x-forms.input
                                        :srOnlyLabel="true"
                                        :required="false"
                                        min="0"
                                        class="text-center px-2"
                                        :label="__('pages/scrims/games/create.kda_death_placeholder')"
                                        :name="'draft_away_'.$row['key'].'_deaths'"
                                        :type="'number'"
                                        :placeholder="__('pages/scrims/games/create.kda_death_placeholder')" />
                                </div>
                                <span class="text-text-secondary" aria-hidden="true">/</span>
                                <div class="flex-1">
                                    <x-forms.input
                                        :srOnlyLabel="true"
                                        :required="false"
                                        min="0"
                                        class="text-center px-2"
                                        :label="__('pages/scrims/games/create.kda_assist_placeholder')"
                                        :name="'draft_away_'.$row['key'].'_assists'"
                                        :type="'number'"
                                        :placeholder="__('pages/scrims/games/create.kda_assist_placeholder')" />
                                </div>
                            </div>
                        </div>
                    </article>
                    @endforeach
                </div>
            </fieldset>
        </div>

    </form>

</section>