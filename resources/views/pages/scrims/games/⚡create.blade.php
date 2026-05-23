<?php

use App\Models\Scrim;
use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\TeamMember;
use Illuminate\Support\Collection;
use App\Livewire\Forms\Scrim\CreateScrimGameForm;
use App\Enums\TypeScrimGameNote;

new #[Layout('layouts::team')] class extends Component
{
    public Scrim $scrim;

    public Collection $teamMembersStarters;

    public Collection $opponentTeamMembersStarters;

    public CreateScrimGameForm $form;

    public string $newPositiveNote = '';

    public string $newNegativeNote = '';

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



        foreach ($this->teamMembersStarters as $member) {
            $this->form->players[$member->id] = [
                'champion' => null,
                'kills' => 0,
                'deaths' => 0,
                'assists' => 0,
            ];
        }

        foreach (['top', 'jungle', 'mid', 'bot', 'support'] as $role) {
            $this->form->opponentTeamMembersStarters[$role] = [
                'champion' => null,
                'kills' => 0,
                'deaths' => 0,
                'assists' => 0,
            ];
        }
    }

    public function addNote(string $type): void
    {
        if ($type === TypeScrimGameNote::POSITIVE->value) {
            $property = 'newPositiveNote';
        } else {
            $property = 'newNegativeNote';
        }

        $note = trim($this->{$property});

        if (mb_strlen($note) < 3) {
            session()->flash(
                $type === TypeScrimGameNote::POSITIVE->value ? 'errorNewPositiveNote' : 'errorNewNegativeNote',
                __('pages/scrims/games/create.error_too_short_note'),
            );

            return;
        }

        $this->form->scrimGameNotes[] = [
            'type' => $type,
            'note' => $note,
        ];

        $this->{$property} = '';
    }

    public function removeNote(int $index): void
    {
        unset($this->form->scrimGameNotes[$index]);
        $this->form->scrimGameNotes = array_values($this->form->scrimGameNotes);
    }

    public function createGame(): void
    {
        if (! $this->form->store($this->scrim->id)) {
            $this->dispatch('toast', [
                'title' => __('policies/scrim.error_title'),
                'message' => __('policies/scrim.error_create_game'),
                'type' => 'error',
            ]);

            return;
        }

        session()->flash('toast', [
            'type' => 'success',
            'message' => __('toasts/toasts.game_created'),
        ]);
        $this->redirect(route('scrims.show', ['id' => $this->scrim->id, 'slug' => currentTeam()->slug]));
    }
};
?>

@php
$teamsSummary =
$this->scrim->team->name.
' vs '.
($this->scrim->opponentTeam?->name ?? __('pages/scrims/show.opponent_unknown'));

$champions = collect(getChampionsList())->sortBy('name')->pluck('name');
@endphp

<section class="flex flex-col gap-8">
    <div class="flex flex-row flex-wrap items-center justify-between gap-4">
        <div class="flex flex-col gap-2">
            <h2 class="text-[32px] font-bold text-text-primary">
                {{ __('pages/scrims/games/create.page_title') }}
            </h2>
            <p class="text-base text-text-secondary">
                {{ __('pages/scrims/games/create.scrim_label', ['teams' => $teamsSummary]) }}
            </p>
        </div>
        <button wire:click="createGame" type="button" class="cta-primary shrink-0" title="{{ __('pages/scrims/games/create.create_button') }}">
            {{ __('pages/scrims/games/create.create_button') }}
        </button>
    </div>
    <form wire:submit="createGame" class="grid grid-cols-12 gap-6">
        <fieldset class="flex flex-col gap-6 border-0 bg-bg-widget p-6 shadow-basic col-span-full">
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
                        wire:model.live="form.title"
                        :srOnlyLabel="true"
                        :required="true"
                        class="w-full"
                        :placeholder="__('pages/scrims/games/create.name_placeholder')"
                        :label="__('pages/scrims/games/create.name_label')"
                        :name="'game-name'"
                        :type="'text'">
                        @error('form.title')
                        <p class="text-red-500">{{ $message }}</p>
                        @enderror
                    </x-forms.input>
                </div>

                {{-- Duration --}}
                <div class="flex w-full flex-col gap-2">
                    <span class="block font-medium text-white">
                        {{ __('pages/scrims/games/create.duration_label') }}
                    </span>
                    <div class="flex items-center gap-4">
                        <div class="flex flex-1 items-center gap-2">
                            <x-forms.input
                                wire:model.live="form.duration_minutes"
                                :srOnlyLabel="true"
                                :required="false"
                                min="0"
                                max="120"
                                :placeholder="'16'"
                                class="w-full"
                                :label="__('pages/scrims/games/create.duration_minutes_aria')"
                                :name="'game-duration-minutes'"
                                :type="'number'">
                                @error('form.duration_minutes')
                                <p class="text-red-500">{{ $message }}</p>
                                @enderror
                            </x-forms.input>
                            <span class="text-text-secondary">{{ __('pages/scrims/games/create.minutes_suffix') }}</span>
                        </div>
                        <div class="flex flex-1 items-center gap-2">
                            <x-forms.input
                                :srOnlyLabel="true"
                                wire:model.live="form.duration_seconds"
                                :required="false"
                                min="0"
                                max="59"
                                :placeholder="'47'"
                                class="w-full"
                                :label="__('pages/scrims/games/create.duration_seconds_aria')"
                                :name="'game-duration-seconds'"
                                :type="'number'">
                                @error('form.duration_seconds')
                                <p class="text-red-500">{{ $message }}</p>
                                @enderror
                            </x-forms.input>
                            <span class="text-text-secondary">{{ __('pages/scrims/games/create.seconds_suffix') }}</span>
                        </div>
                    </div>
                </div>

                {{-- Result --}}
                <div class="flex flex-col gap-3">
                    <x-forms.radio
                        wire:model.live="form.is_victory"
                        variant="outcome"
                        name="game_outcome"
                        :columns="2"
                        :required="true"
                        :label="__('pages/scrims/games/create.result_label')"
                        :options="[
                            [
                                'value' => '1',
                                'label' => __('pages/scrims/games/create.result_win'),
                            ],
                            [
                                'value' => '0',
                                'label' => __('pages/scrims/games/create.result_loss'),
                            ],
                        ]"
                        grid-gap-class="gap-3 sm:gap-4" />
                </div>
            </div>
        </fieldset>
        <div class="col-span-full grid grid-cols-1 gap-8 lg:grid-cols-2 lg:gap-10">
            <fieldset class="flex flex-col gap-6 border-0 bg-bg-widget p-6 shadow-basic">
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
                            wire:model.live="form.players.{{ $teamMember->id }}.champion"
                            :hasLabel="true"
                            :required="true"
                            :name="$teamMember->user->username.'_champion_id'"
                            :label="__('pages/scrims/games/create.champion_label')"
                            :disabled="__('pages/scrims/games/create.champion_select_placeholder')"
                            :options="$champions">
                                @error("form.players.{{ $teamMember->id }}.champion")
                                <p class="text-red-500">{{ $message }}</p>
                                @enderror
                            </x-forms.select>
                        <div class="flex flex-col gap-2">
                            <span class="block font-medium text-white">
                                {{ __('pages/scrims/games/create.score_label') }}
                            </span>
                            <div class="flex items-center justify-center gap-2 sm:justify-start">
                                <div class="flex-1">
                                    <x-forms.input
                                        wire:model.live="form.players.{{ $teamMember->id }}.kills"
                                        :srOnlyLabel="true"
                                        :required="false"
                                        min="0"
                                        class="text-center px-2"
                                        :label="__('pages/scrims/games/create.kda_kill_placeholder')"
                                        :name="$teamMember->user->username.'_kills'"
                                        :type="'number'"
                                        :placeholder="__('pages/scrims/games/create.kda_kill_placeholder')">
                                        @error("form.players.{{ $teamMember->id }}.kills")
                                        <p class="text-red-500">{{ $message }}</p>
                                        @enderror
                                    </x-forms.input>
                                </div>
                                <span class="text-text-secondary" aria-hidden="true">/</span>
                                <div class="flex-1">
                                    <x-forms.input
                                        wire:model.live="form.players.{{ $teamMember->id }}.deaths"
                                        :srOnlyLabel="true"
                                        :required="false"
                                        min="0"
                                        class="text-center px-2"
                                        :label="__('pages/scrims/games/create.kda_death_placeholder')"
                                        :name="$teamMember->user->username.'_deaths'"
                                        :type="'number'"
                                        :placeholder="__('pages/scrims/games/create.kda_death_placeholder')">
                                        @error("form.players.{{ $teamMember->id }}.deaths")
                                        <p class="text-red-500">{{ $message }}</p>
                                        @enderror
                                    </x-forms.input>
                                </div>
                                <span class="text-text-secondary" aria-hidden="true">/</span>
                                <div class="flex-1">
                                    <x-forms.input
                                        :srOnlyLabel="true"
                                        wire:model.live="form.players.{{ $teamMember->id }}.assists"
                                        :required="false"
                                        min="0"
                                        class="text-center px-2"
                                        :label="__('pages/scrims/games/create.kda_assist_placeholder')"
                                        :name="$teamMember->user->username.'_assists'"
                                        :type="'number'"
                                        :placeholder="__('pages/scrims/games/create.kda_assist_placeholder')">
                                        @error("form.players.{{ $teamMember->id }}.assists")
                                        <p class="text-red-500">{{ $message }}</p>
                                        @enderror
                                    </x-forms.input>
                                </div>
                            </div>
                        </div>
                    </article>
                    @endforeach
                </div>
            </fieldset>

            {{-- Line-up adversaire --}}
            <fieldset class="flex flex-col gap-6 border-0 bg-bg-widget p-6 shadow-basic">
                <legend class="sr-only">
                    {{ __('pages/scrims/games/create.draft_away_fieldset_legend') }}
                </legend>
                <h3 class="border-b border-gold pb-4 text-2xl font-bold text-gold">
                    {{ $this->scrim->opponentTeam?->name ?? __('pages/scrims/show.opponent_unknown') }}
                    <span class="font-semibold text-gold">— {{ __('pages/scrims/games/create.score_draft_legend') }}</span>
                </h3>

                <div class="flex flex-col gap-4">
                    @foreach ($this->form->opponentTeamMembersStarters as $role => $player)
                    <article class="flex flex-col gap-4 bg-bg-card p-4 shadow-basic">
                        <div class="flex flex-wrap items-center gap-2">
                            <div class="flex items-center gap-2">
                                <h4 class="text-xl font-semibold text-gold">
                                    {{ __('pages/scrims/games/create.opponent_lineup_name') }}
                                    -
                                    {{ __('pages/scrims/games/create.role_'.$role) }}
                                </h4>
                            </div>
                        </div>

                        <x-forms.select
                            wire:model.live="form.opponentTeamMembersStarters.{{ $role }}.champion"
                            :hasLabel="true"
                            :required="false"
                            :name="'opponent_team_members_starters_'.$role.'_champion'"
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
                                        wire:model.live="form.opponentTeamMembersStarters.{{ $role }}.kills"
                                        :srOnlyLabel="true"
                                        :required="false"
                                        min="0"
                                        class="text-center px-2"
                                        :label="__('pages/scrims/games/create.kda_kill_placeholder')"
                                        :name="'opponent_team_members_starters_'.$role.'_kills'"
                                        :type="'number'"
                                        :placeholder="__('pages/scrims/games/create.kda_kill_placeholder')" />
                                </div>
                                <span class="text-text-secondary" aria-hidden="true">/</span>
                                <div class="flex-1">
                                    <x-forms.input
                                        wire:model.live="form.opponentTeamMembersStarters.{{ $role }}.deaths"
                                        :srOnlyLabel="true"
                                        :required="false"
                                        min="0"
                                        class="text-center px-2"
                                        :label="__('pages/scrims/games/create.kda_death_placeholder')"
                                        :name="'opponent_team_members_starters_'.$role.'_deaths'"
                                        :type="'number'"
                                        :placeholder="__('pages/scrims/games/create.kda_death_placeholder')" />
                                </div>
                                <span class="text-text-secondary" aria-hidden="true">/</span>
                                <div class="flex-1">
                                    <x-forms.input
                                        wire:model.live="form.opponentTeamMembersStarters.{{ $role }}.assists"
                                        :srOnlyLabel="true"
                                        :required="false"
                                        min="0"
                                        class="text-center px-2"
                                        :label="__('pages/scrims/games/create.kda_assist_placeholder')"
                                        :name="'opponent_team_members_starters_'.$role.'_assists'"
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

        {{-- Notes de la game --}}
        <fieldset class="col-span-full flex flex-col gap-6 border-0 bg-bg-widget p-6 shadow-basic">
            <legend class="sr-only">
                {{ __('pages/scrims/games/create.notes_fieldset_legend') }}
            </legend>
            <h3 class="border-b border-gold pb-4 text-2xl font-bold text-gold">
                {{ __('pages/scrims/games/create.notes_fieldset_legend') }}
            </h3>

            <div class="grid grid-cols-1 gap-8 lg:grid-cols-2 lg:gap-10">
                {{-- Points positifs --}}
                <div x-data="{ addNewPositiveNote: false }" class="flex flex-col gap-4">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <flux:icon name="plus-circle" variant="solid" class="size-6 shrink-0 text-green-500" />
                            <h4 class="text-xl font-semibold text-white">
                                {{ __('pages/scrims/games/create.positive_notes_title') }}
                            </h4>
                            <span class="inline-flex min-w-8 items-center justify-center  bg-bg-card px-2 py-0.5 font-semibold text-green-500">
                                {{ collect($this->form->scrimGameNotes)->where('type', TypeScrimGameNote::POSITIVE->value)->count() }}
                            </span>
                        </div>
                        <button
                            @click="addNewPositiveNote = true"
                            class="cta-primary shrink-0 cursor-pointer"
                            type="button">
                            {{ __('pages/scrims/games/create.add_note') }}
                        </button>
                    </div>

                    @php
                        $positiveNotes = collect($this->form->scrimGameNotes)
                            ->filter(fn (array $note): bool => $note['type'] === TypeScrimGameNote::POSITIVE->value);
                    @endphp

                    @if ($positiveNotes->isNotEmpty())
                        <ul class="flex flex-col gap-2" role="list">
                            @foreach ($this->form->scrimGameNotes as $index => $note)
                                @if ($note['type'] === TypeScrimGameNote::POSITIVE->value)
                                    <li wire:key="scrim-game-note-positive-{{ $index }}" class="flex items-center justify-between gap-3 bg-bg-card p-4">
                                        <span class="text-white">{{ $note['note'] }}</span>
                                        <button
                                            type="button"
                                            wire:click="removeNote({{ $index }})"
                                            class="cursor-pointer text-text-secondary transition-all duration-150 hover:text-red-500">
                                            <flux:icon name="x-mark" class="size-5" />
                                        </button>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    @else
                        <p class="text-text-secondary">
                            {{ __('pages/scrims/games/create.no_positive_notes') }}
                        </p>
                    @endif

                    @error('form.scrimGameNotes.*.note')
                        <p class="text-red-500 font-bold text-sm">{{ $message }}</p>
                    @enderror

                    <div x-show="addNewPositiveNote" x-cloak class="flex flex-col gap-2">
                        <div class="flex items-end gap-2">
                            <x-forms.input
                                :required="false"
                                @keydown.enter.prevent="$wire.addNote('{{ TypeScrimGameNote::POSITIVE->value }}')"
                                :type="'text'"
                                wire:model="newPositiveNote"
                                :label="__('pages/scrims/games/create.field_note_content')"
                                :name="'new_note_positive'"
                                :placeholder="__('pages/scrims/games/create.field_note_placeholder')"
                                class="w-full" />
                            <button
                                type="button"
                                x-on:click="addNewPositiveNote = false"
                                wire:click="addNote('{{ TypeScrimGameNote::POSITIVE->value }}')"
                                class="cta-primary shrink-0 cursor-pointer self-end">
                                {{ __('pages/scrims/games/create.add_note') }}
                            </button>
                        </div>
                        @if (session('errorNewPositiveNote'))
                            <p class="text-red-500 font-bold text-sm">{{ session('errorNewPositiveNote') }}</p>
                        @endif
                    </div>
                </div>

                {{-- Points négatifs --}}
                <div x-data="{ addNewNegativeNote: false }" class="flex flex-col gap-4">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <flux:icon name="minus-circle" variant="solid" class="size-6 shrink-0 text-red-500" />
                            <h4 class="text-xl font-semibold text-white">
                                {{ __('pages/scrims/games/create.negative_notes_title') }}
                            </h4>
                            <span class="inline-flex min-w-8 items-center justify-center  bg-bg-card px-2 py-0.5 font-semibold text-red-500">
                                {{ collect($this->form->scrimGameNotes)->where('type', TypeScrimGameNote::NEGATIVE->value)->count() }}
                            </span>
                        </div>
                        <button
                            @click="addNewNegativeNote = true"
                            class="cta-primary shrink-0 cursor-pointer"
                            type="button">
                            {{ __('pages/scrims/games/create.add_note') }}
                        </button>
                    </div>

                    @php
                        $negativeNotes = collect($this->form->scrimGameNotes)
                            ->filter(fn (array $note): bool => $note['type'] === TypeScrimGameNote::NEGATIVE->value);
                    @endphp

                    @if ($negativeNotes->isNotEmpty())
                        <ul class="flex flex-col gap-2" role="list">
                            @foreach ($this->form->scrimGameNotes as $index => $note)
                                @if ($note['type'] === TypeScrimGameNote::NEGATIVE->value)
                                    <li wire:key="scrim-game-note-negative-{{ $index }}" class="flex items-center justify-between gap-3 bg-bg-card p-4">
                                        <span class="text-white">{{ $note['note'] }}</span>
                                        <button
                                            type="button"
                                            wire:click="removeNote({{ $index }})"
                                            class="cursor-pointer text-text-secondary transition-all duration-150 hover:text-red-500">
                                            <flux:icon name="x-mark" class="size-5" />
                                        </button>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    @else
                        <p class="text-text-secondary">
                            {{ __('pages/scrims/games/create.no_negative_notes') }}
                        </p>
                    @endif

                    @error('form.scrimGameNotes.*.note')
                        <p class="text-red-500 font-bold text-sm">{{ $message }}</p>
                    @enderror

                    <div x-show="addNewNegativeNote" x-cloak class="flex flex-col gap-2">
                        <div class="flex items-end gap-2">
                            <x-forms.input
                                :required="false"
                                @keydown.enter.prevent="$wire.addNote('{{ TypeScrimGameNote::NEGATIVE->value }}')"
                                :type="'text'"
                                wire:model="newNegativeNote"
                                :label="__('pages/scrims/games/create.field_note_content')"
                                :name="'new_note_negative'"
                                :placeholder="__('pages/scrims/games/create.field_note_placeholder')"
                                class="w-full" />
                            <button
                                type="button"
                                x-on:click="addNewNegativeNote = false"
                                wire:click="addNote('{{ TypeScrimGameNote::NEGATIVE->value }}')"
                                class="cta-primary shrink-0 cursor-pointer self-end">
                                {{ __('pages/scrims/games/create.add_note') }}
                            </button>
                        </div>
                        @if (session('errorNewNegativeNote'))
                            <p class="text-red-500 font-bold text-sm">{{ session('errorNewNegativeNote') }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </fieldset>
        <div class="flex bg-bg-widget p-6 shadow-basic flex-row flex-wrap items-center justify-between gap-4 col-span-full">
            <x-cta wire:navigate :href="route('scrims.show', ['id' => $this->scrim->id, 'slug' => currentTeam()->slug])" :title="__('pages/scrims/games/create.cancel_button')" :class="'secondary'">
                {{ __('pages/scrims/games/create.cancel_button') }}
            </x-cta>
            <x-forms.submit>
                {{ __('pages/scrims/games/create.create_button') }}
            </x-cta>
        </div>
    </form>

</section>