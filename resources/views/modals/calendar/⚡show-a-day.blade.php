<?php

use Livewire\Component;
use App\Models\Scrim;
use App\Models\Event;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use App\Models\TeamMember;
use App\Enums\RoleInTeam;
use App\Enums\TypeEvents;
use App\Livewire\Forms\Calendar\CreateEventForm;

new class extends Component
{
    public Collection $allScrimsThisDay;

    public Collection $allEventsThisDay;

    public string $date;

    public int $dayOfWeek;

    public Collection $allTeamMembersAvailabilitiesThisDay;

    public CreateEventForm $form;

    public function mount(string $model_id): void
    {
        $this->date = Carbon::parse($model_id)->format('Y-m-d');
        $this->loadDayData();
    }

    public function loadDayData(): void
    {
        $this->allScrimsThisDay = Scrim::where('team_id', currentTeam()->id)
            ->whereDate('scheduled_date', $this->date)
            ->get();

        $this->allEventsThisDay = Event::query()
            ->where('team_id', currentTeam()->id)
            ->whereDate('date', $this->date)
            ->get();

        $dateForPlayerDefaultSchedules = Carbon::parse($this->date);
        $this->dayOfWeek = $dateForPlayerDefaultSchedules->isoWeekday() - 1;
        $this->allTeamMembersAvailabilitiesThisDay = TeamMember::with('user:id,username', 'playerDefaultSchedules')
            ->where('team_id', currentTeam()->id)
            ->where('roleInTeam', RoleInTeam::PLAYER)
            ->get();
    }

    public function storeEvent(): void
    {
        $this->form->store($this->date, currentTeam()->id);

        $this->form->reset();
        $this->loadDayData();

        $this->dispatch('event-created');
        $this->dispatch('toast', [
            'title' => __('modals/calendar/create-event.success_title'),
            'message' => __('modals/calendar/create-event.success_message'),
            'type' => 'success',
        ]);
    }
};
?>

<div
    x-data="{ showCreateEvent: false }"
    @event-created.window="showCreateEvent = false">
    <x-layout.head-modal
        :width="'3xl'"
        :height="'75'"
        :title="Carbon::parse($date)->isoFormat('D MMMM YYYY')">
        <div class="flex flex-col gap-6">
            {{-- Scrims --}}
            <x-accordion
                :title="__('modals/calendar/show-a-day.scrims_title')"
                :open="true"
                :count="$allScrimsThisDay->count()"
                heading-level="h3">
                @if ($allScrimsThisDay->isEmpty())
                <li class="col-span-12">
                    <p class="text-sm text-text-secondary">
                        {{ __('modals/calendar/show-a-day.no_scrims') }}
                    </p>
                </li>
                @else
                @foreach ($allScrimsThisDay as $scrim)
                @php
                $opponent = $scrim->opponentTeam;
                $scheduledAt = $scrim->scheduled_at;
                @endphp
                <li class="col-span-12" wire:key="calendar-scrim-{{ $scrim->id }}">
                    <article class="relative flex flex-col border-l-2 border-gold bg-bg-card p-4 basic-shadow md:p-5 card-animated-border">
                        <span class="card-animated-border-right-edge" aria-hidden="true"></span>
                        <div class="relative z-[1] flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div class="min-w-0">
                                <h4 class="text-xl font-bold text-white">
                                    {{ $opponent?->name ?? __('modals/calendar/show-a-day.opponent_unknown') }}
                                </h4>
                                <p class="mt-1 text-sm text-text-secondary">
                                    {{ __('modals/calendar/show-a-day.scrim_format', [
                                                'time' => $scheduledAt->format('H:i'),
                                                'games' => $scrim->number_of_games,
                                            ]) }}
                                </p>
                            </div>
                            <x-cta
                                wire:navigate
                                :href="route('scrims.show', ['slug' => currentTeam()->slug, 'id' => $scrim->id])"
                                :class="'primary'"
                                :title="__('modals/calendar/show-a-day.show_scrim_title')">
                                {{ __('modals/calendar/show-a-day.show_scrim') }}
                            </x-cta>
                        </div>
                    </article>
                </li>
                @endforeach
                @endif
            </x-accordion>

            {{-- Événements --}}
            <x-accordion
                :title="__('modals/calendar/show-a-day.events_title')"
                :open="true"
                :count="$allEventsThisDay->count()"
                heading-level="h3">
                <x-slot:actions>
                    <button
                        type="button"
                        @click="showCreateEvent = true"
                        class="cta-primary whitespace-nowrap px-4 py-2 text-sm"
                        title="{{ __('modals/calendar/show-a-day.create_event_title') }}">
                        {{ __('modals/calendar/show-a-day.create_event') }}
                    </button>
                </x-slot:actions>

                @if ($allEventsThisDay->isEmpty())
                <li class="col-span-12">
                    <p class="text-sm text-text-secondary">
                        {{ __('modals/calendar/show-a-day.no_events') }}
                    </p>
                </li>
                @else
                @foreach ($allEventsThisDay as $event)
                <li class="col-span-12" wire:key="calendar-event-{{ $event->id }}">
                    <article
                        class="relative flex flex-col border-l-2 bg-bg-card p-4 basic-shadow md:p-5"
                        style="border-left-color: {{ $event->type->color() }}">
                        <div class="relative z-[1] flex flex-col gap-2">
                            <h4 class="text-xl font-bold text-white">
                                {{ $event->title }}
                            </h4>
                            <p class="text-sm text-text-secondary">
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
                    </article>
                </li>
                @endforeach
                @endif
            </x-accordion>

            {{-- Disponibilités joueurs --}}
            <x-accordion
                :title="__('modals/calendar/show-a-day.availabilities_title')"
                :open="true"
                :count="$allTeamMembersAvailabilitiesThisDay->count()"
                heading-level="h3"
                panel-class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-3">
                @if ($allTeamMembersAvailabilitiesThisDay->isEmpty())
                <li class="col-span-2 sm:col-span-3">
                    <p class="text-sm text-text-secondary">
                        {{ __('modals/calendar/show-a-day.no_availabilities') }}
                    </p>
                </li>
                @else
                @foreach ($allTeamMembersAvailabilitiesThisDay as $teamMember)
                <li wire:key="calendar-availability-{{ $teamMember->id }}">
                    <article class="flex items-center gap-3 bg-bg-card p-4 basic-shadow">
                        @if ($teamMember->absences->where('date', Carbon::parse($this->date))->isNotEmpty())
                        <span
                            class="size-2.5 shrink-0 rounded-full bg-red-500"
                            aria-hidden="true"></span>
                        @elseif ($teamMember->playerDefaultSchedules->where('day_of_week', $dayOfWeek)->isNotEmpty())
                        <span
                            class="size-2.5 shrink-0 rounded-full bg-green-500"
                            aria-hidden="true"></span>
                        @else
                        <span
                            class="size-2.5 shrink-0 rounded-full bg-gray-500"
                            aria-hidden="true"></span>
                        @endif
                        <span class="truncate text-sm font-medium text-white">
                            {{ $teamMember->user->username }}
                        </span>
                    </article>
                </li>
                @endforeach
                @endif
            </x-accordion>
        </div>
    </x-layout.head-modal>

    <x-layout.nested-modal
        show="showCreateEvent"
        :width="'2xl'"
        :title="__('modals/calendar/create-event.title')">
        <form wire:submit="storeEvent" class="flex flex-col gap-6" wire:click.stop>
            <x-forms.input
                wire:model.live="form.title"
                name="event-title"
                type="text"
                :placeholder="__('modals/calendar/create-event.field_title_placeholder')"
                :label="__('modals/calendar/create-event.field_title')"
                :required="true">
                @error('form.title')
                    <span class="font-spaceGrotesk text-sm font-semibold text-input-error">{{ $message }}</span>
                @enderror
            </x-forms.input>

            <label class="flex w-fit cursor-pointer items-center gap-3">
                <input
                    type="checkbox"
                    wire:model.live="form.all_day"
                    name="event-all-day"
                    class="size-5 shrink-0 cursor-pointer rounded border-2 border-gold-border bg-input-bg accent-gold">
                <span class="text-base font-medium text-white">
                    {{ __('modals/calendar/create-event.all_day') }}
                </span>
            </label>
            @error('form.all_day')
                <span class="font-spaceGrotesk text-sm font-semibold text-input-error">{{ $message }}</span>
            @enderror

            <div>
                @if (! $form->all_day)
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <x-forms.input
                        wire:model.live="form.start_time"
                        name="event-start-time"
                        type="time"
                        :label="__('modals/calendar/create-event.start_time')"
                        :required="true">
                        @error('form.start_time')
                            <span class="font-spaceGrotesk text-sm font-semibold text-input-error">{{ $message }}</span>
                        @enderror
                    </x-forms.input>
                    <x-forms.input
                        wire:model.live="form.end_time"
                        name="event-end-time"
                        type="time"
                        :label="__('modals/calendar/create-event.end_time')"
                        :required="true">
                        @error('form.end_time')
                            <span class="font-spaceGrotesk text-sm font-semibold text-input-error">{{ $message }}</span>
                        @enderror
                    </x-forms.input>
                </div>
                @endif
            </div>

            <x-forms.radio
                wire:model.live="form.type"
                name="event-type"
                :label="__('modals/calendar/create-event.type')"
                :options="TypeEvents::cases()"
                :columns="4"
                :required="true">
                @error('form.type')
                    <span class="font-spaceGrotesk text-sm font-semibold text-input-error">{{ $message }}</span>
                @enderror
            </x-forms.radio>

            <div class="flex flex-col-reverse gap-3 pt-2 sm:flex-row sm:justify-between sm:pt-4">
                <button
                    type="button"
                    @click="showCreateEvent = false"
                    class="cta-secondary w-full sm:w-auto"
                    title="{{ __('modals/calendar/create-event.cancel_title') }}">
                    {{ __('modals/calendar/create-event.cancel') }}
                </button>
                <button
                    type="submit"
                    class="cta-primary w-full cursor-pointer sm:w-auto"
                    title="{{ __('modals/calendar/create-event.create_title') }}">
                    {{ __('modals/calendar/create-event.create') }}
                </button>
            </div>
        </form>
    </x-layout.nested-modal>
</div>
