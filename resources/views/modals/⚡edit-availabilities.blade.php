<?php

use App\Enums\DayOfTheWeek;
use App\Livewire\Forms\EditAvailabilitiesForm;
use App\Models\TeamMember;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

new class extends Component
{
    public TeamMember $teamMember;

    public EditAvailabilitiesForm $form;

    public function mount($model_id): void
    {
        $this->teamMember = TeamMember::query()
            ->with('playerDefaultSchedules')
            ->whereKey($model_id)
            ->where('team_id', currentTeam()->id)
            ->firstOrFail();

        abort_unless(Gate::allows('manageAvailability', $this->teamMember), 403);

        foreach (DayOfTheWeek::cases() as $day) {
            $this->form->slotEnabled[$day->value] = false;
        }

        foreach ($this->teamMember->playerDefaultSchedules as $schedule) {
            $dayValue = (int) $schedule->day_of_week;
            $this->form->slotEnabled[$dayValue] = true;
            $this->form->startTimes[$dayValue] = Carbon::parse($schedule->start_time)->format('H:i');
            $this->form->endTimes[$dayValue] = Carbon::parse($schedule->end_time)->format('H:i');
        }
    }

    public function closeModal(): void
    {
        $this->dispatch('close_modal');
    }

    public function saveAvailabilities(): void
    {
        abort_unless(Gate::allows('manageAvailability', $this->teamMember), 403);

        $this->form->saveAvailabilities($this->teamMember->id);
        $this->dispatch('close_modal');
        $this->dispatch('refresh_default_schedules');
        $this->dispatch('toast', [
            'title' => __('modals/edit-availabilities.success_title'),
            'message' => __('modals/edit-availabilities.success_message'),
            'type' => 'success',
        ]);
    }
};
?>

<div>
    @php
    $timeSlots = [];
    for ($h = 8; $h <= 23; $h++) {
        $timeSlots[]=str_pad((string) $h, 2, '0' , STR_PAD_LEFT).':00';
        if ($h < 23) {
        $timeSlots[]=str_pad((string) $h, 2, '0' , STR_PAD_LEFT).':30';
        }
        }
        $timeSlotOptions=array_combine($timeSlots, $timeSlots);
        @endphp

        <x-layout.head-modal :width="'2xl'" :height="'75'" :title="__('modals/edit-availabilities.title')">

        <form class="flex flex-col gap-4" wire:submit.prevent="saveAvailabilities" wire:click.stop>
            <fieldset class="m-0 flex flex-col gap-4 border-0 p-0">
                <legend class="sr-only">{{ __('modals/edit-availabilities.title') }}</legend>
            @foreach (DayOfTheWeek::cases() as $day)
            @php
            $enabled = (bool) ($form->slotEnabled[$day->value] ?? false);
            @endphp
            <fieldset
                wire:key="availability-row-{{ $day->value }}"
                @class([ 'flex flex-col gap-4 sm:gap-3 sm:justify-between sm:items-center shadow-basic bg-bg-widget p-6 transition-opacity sm:flex-row sm:flex-wrap  sm:items-center sm:gap-x-3' , 'opacity-80'=> ! $enabled,
                ])
                >
                <legend class="sr-only">
                    {{ __('modals/edit-availabilities.day') }} {{ $day->label() }}
                </legend>
                <div class="flex flex-col gap-4 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between sm:gap-x-3">
                    <label class="flex min-w-0 cursor-pointer items-center gap-3 sm:w-36">
                        <input
                            type="checkbox"
                            wire:model.live="form.slotEnabled.{{ $day->value }}"
                            class="size-5 shrink-0 cursor-pointer rounded border-2 border-gold-border bg-input-bg accent-gold">
                        <span
                            @class([
                                'text-base font-medium',
                                'text-text-primary' => $form->slotEnabled[$day->value],
                                'text-text-secondary' => ! $form->slotEnabled[$day->value],
                            ])>
                            {{ $day->label() }}
                        </span>
                    </label>
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                        <x-forms.select
                            :label="__('modals/edit-availabilities.from')"
                            :name="'availability-start-'.$day->value"
                            :options="$timeSlotOptions"
                            :disabled="'-- : --'"
                            :inputDisabled="! $form->slotEnabled[$day->value]"
                            :labelNextToSelect="true"
                            wire:model.live="form.startTimes.{{ $day->value }}" />
                        <x-forms.select
                            :label="__('modals/edit-availabilities.to')"
                            :name="'availability-end-'.$day->value"
                            :options="$timeSlotOptions"
                            :disabled="'-- : --'"
                            :inputDisabled="! $form->slotEnabled[$day->value]"
                            :labelNextToSelect="true"
                            wire:model.live="form.endTimes.{{ $day->value }}" />
                    </div>
                </div>
                @if ($errors->has("form.startTimes.{$day->value}") || $errors->has("form.endTimes.{$day->value}"))
                    <div class="flex flex-col gap-1">
                        @error("form.startTimes.{$day->value}")
                            <span class="font-spaceGrotesk text-input-error font-semibold">
                                {{ $message }}
                            </span>
                        @enderror
                        @error("form.endTimes.{$day->value}")
                            <span class="font-spaceGrotesk text-input-error font-semibold">
                                {{ $message }}
                            </span>
                        @enderror
                    </div>
                @endif
            </fieldset>

            @endforeach
            </fieldset>

            <div class="flex flex-col-reverse gap-3 pt-2 sm:flex-row sm:justify-between sm:pt-4">
                <button
                    type="button"
                    wire:click="closeModal"
                    class="cta-secondary w-full sm:w-auto">
                    {{ __('modals/edit-availabilities.cancel') }}
                </button>
                <x-forms.submit class="w-full sm:w-auto">
                    {{ __('modals/edit-availabilities.save') }}
                </x-forms.submit>
            </div>
        </form>
        </x-layout.head-modal>
</div>