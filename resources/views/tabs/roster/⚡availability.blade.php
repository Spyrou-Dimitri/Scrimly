<?php

use App\Enums\DayOfTheWeek;
use App\Models\TeamMember;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    #[Locked]
    public TeamMember $teamMember;

    public Collection $defaultSchedules;

    public Collection $absences;

    public function mount(TeamMember $teamMember): void
    {
        $this->teamMember = $teamMember;
        $this->defaultSchedules = $teamMember->playerDefaultSchedules()->get();
        $this->absences = $teamMember->absences()->orderBy('date', 'asc')->get();
    }

    #[Computed]
    public function canManageAvailability(): bool
    {
        return Gate::allows('manageAvailability', $this->teamMember);
    }

    private function denyIfCannotManageAvailability(): void
    {
        abort_unless(Gate::allows('manageAvailability', $this->teamMember), 403);
    }

    public function openModalAddAvailability(): void
    {
        $this->denyIfCannotManageAvailability();
        $this->dispatch('open_modal', [
            'form' => 'edit-availabilities',
            'model_id' => $this->teamMember->id,
            
        ]);
    }

    public function openModalAddAbsence(): void
    {
        $this->denyIfCannotManageAvailability();
        $this->dispatch('open_modal', [
            'form' => 'modals::absence.add-absence',
            'model_id' => $this->teamMember->id,
        ]);
    }

    public function openModalEditAbsence(int $absenceId): void
    {
        $this->denyIfCannotManageAvailability();
        $this->dispatch('open_modal', [
            'form' => 'modals::absence.edit-absence',
            'model_id' => $absenceId,
        ]);
    }

    public function openModalDeleteAbsence(int $absenceId): void
    {
        $this->denyIfCannotManageAvailability();
        $this->dispatch('open_modal', [
            'form' => 'modals::absence.delete-absence',
            'model_id' => $absenceId,
        ]);
    }

    #[On('refresh_default_schedules')]
    public function refreshDefaultSchedules(): void
    {
        $this->defaultSchedules = $this->teamMember->playerDefaultSchedules()->get();
    }

    #[On('refresh_absences')]
    public function refreshAbsences(): void
    {
        $this->absences = $this->teamMember->absences()->get();
    }
}; ?>
<div class="flex flex-col gap-8 lg:grid lg:grid-cols-12 lg:gap-8">
    {{-- Section calendrier --}}
    <section class="flex flex-col gap-6 lg:col-span-8">
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
            <h3 class="font-spaceGrotesk text-2xl font-bold text-white">
                {{ __('pages/roster/show.availability.section_title') }}
            </h3>
            @if ($this->canManageAvailability)
            <button wire:click="openModalAddAvailability" class="cta-primary">
                {{ __('pages/roster/show.availability.add_availability') }}
            </button>
            @endif
        </div>

        <div class="grid grid-cols-[auto_repeat(7,1fr)] gap-x-4 bg-bg-widget p-6 shadow-basic"
             style="grid-template-rows: auto repeat({{ $totalSlots }}, minmax(20px, 1fr));">
            {{-- En-tête des jour --}}
            @foreach ($days as $day)
                <div class="row-start-1 border-b border-[#2A2A2A] py-3 text-center font-semibold text-white"
                     style="grid-column-start: {{ $loop->index + 2 }}">
                    {{ $day }}
                </div>
            @endforeach

            {{-- Colonne des heure --}}
            @for ($h = $startHour; $h <= $endHour; $h++)
                @php
                    $row = ($h - $startHour) * $slotPerHour + 2;
                @endphp
                <div class="col-start-1 pr-3 text-right text-xs text-[#A0A0A0]"
                     style="grid-row-start: {{ $row }}">
                    {{ str_pad($h, 2, '0', STR_PAD_LEFT) }}:00
                </div>
            @endfor

            {{-- Lignes de séparation --}}
            @for ($h = $startHour; $h <= $endHour; $h++)
                @php
                    $row = ($h - $startHour) * $slotPerHour + 2;
                @endphp
                <div class="relative z-0 col-start-2 col-end-9 border-t border-[#2A2A2A]/50"
                     style="grid-row-start: {{ $row }}">
                </div>
            @endfor

            {{-- Bloc des disponibilité --}}
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
                <div class="relative z-10 flex flex-col items-center justify-center rounded border border-emerald-500/40 bg-emerald-500/20 p-2 text-sm font-medium text-white"
                     style="grid-column-start: {{ $day }}; grid-row-start: {{ $rowStart }}; grid-row-end: {{ $rowEnd }}"
                     wire:key="default-schedule-{{ $schedule->id }}">
                    <span>{{ $schedule->start_time }}</span>
                    <span>{{ $schedule->end_time }}</span>
                </div>
            @endforeach

        </div> {{-- Fermeture de la grille --}}
    </section> {{-- Fermeture section calendrier --}}

    {{-- Section absence --}}
    <section class="flex flex-col gap-6 lg:col-span-4">
        <div class="flex justify-between items-center gap-4">
            <h3 class="font-spaceGrotesk text-2xl font-bold text-white">
                {{ __('modals/edit-availabilities.absence_title') }}
            </h3>
            @if ($this->canManageAvailability)
            <button wire:click="openModalAddAbsence" class="cta-primary">
                {{ __('modals/edit-availabilities.add_absence') }}
            </button>
            @endif
        </div>
        <ul class="grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-1">
            @if ($this->absences->isEmpty())
                <p class="col-span-full text-center text-sm text-gray-500">
                    {{ __('modals/edit-availabilities.no_absence') }}
                </p>
            @else
            @foreach ($this->absences as $absence)
                <li class="flex min-w-0 justify-between gap-4 bg-bg-widget p-6 shadow-basic items-center">
                    <div class="flex flex-col gap-2">
                        <p @class(["font-medium", $absence->justification->color()])>
                            {{ $absence->date_formatted }}
                        </p>
                        <p class="text-xl font-bold">{{ $absence->justification->label() }}</p>
                    </div>
                    @if ($this->canManageAvailability)
                    <div class="flex items-center gap-2">
                        <button class="cursor-pointer hover:text-gold transition-colors duration-150"
                                wire:click="openModalEditAbsence({{ $absence->id }})">
                            <x-flux::icon name="pencil" class="w-5 h-5" />
                        </button>
                        <button class="cursor-pointer hover:text-red-700/90 transition-colors duration-150"
                                wire:click="openModalDeleteAbsence({{ $absence->id }})">
                            <x-flux::icon name="trash" class="w-5 h-5" />
                        </button>
                    </div>
                    @endif
                </li>
            @endforeach
            @endif
        </ul>
    </section> 
</div>