<?php

use App\Enums\DayOfTheWeek;
use App\Models\TeamMember;
use Illuminate\Support\Collection;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    #[Locked]
    public TeamMember $teamMember;

    public Collection $defaultSchedules;

    public function mount(TeamMember $teamMember): void
    {
        $this->teamMember = $teamMember;
        $this->defaultSchedules = $teamMember->playerDefaultSchedules()->get();
    }

    public function openModalAddAvailability(): void
    {
        $this->dispatch('open_modal', [
            'form' => 'edit-availabilities',
            'model_id' => $this->teamMember->id,
        ]);
    }

    #[On('refresh_default_schedules')]
    public function refreshDefaultSchedules(): void
    {
        $this->defaultSchedules = $this->teamMember->playerDefaultSchedules()->get();
    }
}; ?>

<section class="flex flex-col gap-4">
    @php
        $startHour = 8;
        $endHour = 23;
        $slotPerHour = 2;
        $totalSlots = ($endHour - $startHour) * $slotPerHour;
        $days = [];
        foreach (DayOfTheWeek::cases() as $day) {
            $days[] = $day->truncatedLabel();
        }
    @endphp

    <div class="flex flex-row justify-between gap-4">
        <h3 id="roster-availability-heading" class="font-spaceGrotesk text-2xl font-bold text-white">
            {{ __('pages/roster/show.availability.section_title') }}
        </h3>
        <button wire:click="openModalAddAvailability" class="cta-primary">
            {{ __('pages/roster/show.availability.add_availability') }}
        </button>
    </div>

    <div class="grid grid-cols-[auto_repeat(7,1fr)] gap-x-4 bg-bg-widget p-6 shadow-basic" style="grid-template-rows: auto repeat({{ $totalSlots }}, minmax(20px, 1fr));">
        {{-- En-têtes des jours --}}
        @foreach ($days as $day)
            <div
                class="row-start-1 border-b border-[#2A2A2A] py-3 text-center font-semibold text-white"
                style="grid-column-start: {{ $loop->index + 2 }}">
                {{ $day }}
            </div>
        @endforeach

        {{-- Colonne des heures --}}
        @for ($h = $startHour; $h <= $endHour; $h++)
            @php
                $row = ($h - $startHour) * $slotPerHour + 2;
            @endphp
            <div
                class="col-start-1 pr-3 text-right text-xs text-[#A0A0A0]"
                style="grid-row-start: {{ $row }}">
                {{ str_pad($h, 2, '0', STR_PAD_LEFT) }}:00
            </div>
        @endfor

        {{-- Lignes de séparation --}}
        @for ($h = $startHour; $h <= $endHour; $h++)
            @php
                $row = ($h - $startHour) * $slotPerHour + 2;
            @endphp
            <div
                class="relative z-0 col-start-2 col-end-9 border-t border-[#2A2A2A]/50"
                style="grid-row-start: {{ $row }}">
            </div>
        @endfor

        {{-- Blocs de disponibilité --}}
        @foreach ($defaultSchedules as $schedule)
            @php
                $day = $schedule->day_of_week + 2;

                $startPart = explode(':', $schedule->start_time);
                $endPart = explode(':', $schedule->end_time);

                $startMinutes = $startPart[0] * 60 + $startPart[1];
                $endMinutes = $endPart[0] * 60 + $endPart[1];

                $rowStart = ($startMinutes - $startHour * 60) / 30 + 2;
                $rowEnd = ($endMinutes - $startHour * 60) / 30 + 2;
            @endphp
            <div
                class="relative z-10 flex flex-col items-center justify-center rounded border border-emerald-500/40 bg-emerald-500/20 p-2 text-sm font-medium text-white"
                style="grid-column-start: {{ $day }}; grid-row-start: {{ $rowStart }}; grid-row-end: {{ $rowEnd }}"
                wire:key="default-schedule-{{ $schedule->id }}">
                <span>{{ $schedule->start_time }}</span>
                <span>{{ $schedule->end_time }}</span>
            </div>
        @endforeach
    </div>
</section>
