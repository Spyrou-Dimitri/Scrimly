<?php

use Livewire\Component;
use App\Models\Scrim;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use App\Models\TeamMember;
use App\Enums\RoleInTeam;

new class extends Component
{
    public Collection $allScrimsThisDay;

    public string $date;
    public int $dayOfWeek;
    public Collection $allTeamMembersAvailabilitiesThisDay;

    public function mount(string $model_id): void
    {
        $this->date = Carbon::parse($model_id)->format('Y-m-d');
        $this->allScrimsThisDay = Scrim::where('team_id', currentTeam()->id)
            ->whereDate('scheduled_date', $this->date)
            ->get();

        $dateForPlayerDefaultSchedules = Carbon::parse($this->date);
        $this->dayOfWeek = $dateForPlayerDefaultSchedules->isoWeekday() - 1;
        $this->allTeamMembersAvailabilitiesThisDay = TeamMember::with('user:id,username', 'playerDefaultSchedules')
            ->where('team_id', currentTeam()->id)
            ->where('roleInTeam', RoleInTeam::PLAYER)
            ->get();
    }
};
?>

<div>
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

            {{-- Événements (design — données à brancher) --}}

            <x-accordion
                :title="__('modals/calendar/show-a-day.events_title')"
                :open="true"
                heading-level="h3">
                <x-slot:actions>
                    <button
                        type="button"
                        class="cta-primary whitespace-nowrap px-4 py-2 text-sm"
                        title="{{ __('modals/calendar/show-a-day.create_event_title') }}">
                        {{ __('modals/calendar/show-a-day.create_event') }}
                    </button>
                </x-slot:actions>


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
</div>