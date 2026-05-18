<?php

use App\Models\Scrim;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::team')] class extends Component
{
    public Scrim $scrim;

    public string $gameName = 'Game 1';

    public string $durationMinutes = '20';

    public string $durationSeconds = '53';

    public ?string $outcome = null;

    public string $side_of_your_team = 'blue_side';

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
};
?>

@php
$teamsSummary =
$this->scrim->team->name.
' vs '.
($this->scrim->opponentTeam?->name ?? __('pages/scrims/show.opponent_unknown'));
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
    <form action="">
        <fieldset class="min-w-0 space-y-6 border-0 bg-bg-widget p-6 shadow-basic">
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
                        wire:model.live="outcome"
                    />
                </div>
            </div>
        </fieldset>
    </form>

</section>