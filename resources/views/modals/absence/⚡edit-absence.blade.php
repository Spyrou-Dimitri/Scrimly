<?php

use App\Enums\AbsenceJustification;
use App\Livewire\Forms\Absence\EditAbsenceForm;
use App\Models\Absence;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

new class extends Component
{
    public Absence $absence;

    public EditAbsenceForm $form;

    public function mount(int $model_id): void
    {
        $this->absence = Absence::query()
            ->whereKey($model_id)
            ->whereHas('teamMember', fn ($query) => $query->where('team_id', currentTeam()->id))
            ->firstOrFail();

        abort_unless(Gate::allows('manageAvailability', $this->absence->teamMember), 403);

        $this->form->setAbsence($this->absence);
    }

    public function editAbsence(): void
    {
        abort_unless(Gate::allows('manageAvailability', $this->absence->teamMember), 403);

        $this->form->saveAbsence();

        $this->dispatch('close_modal');
        $this->dispatch('refresh_absences');
        $this->dispatch('toast', [
            'title' => __('modals/absence/edit-absence.success_title'),
            'message' => __('modals/absence/edit-absence.success_message'),
            'type' => 'success',
        ]);
    }
};
?>

<div>
    <x-layout.head-modal :width="'xl'" :title="__('modals/absence/edit-absence.title')">
        <form wire:submit.prevent="editAbsence" class="flex w-full flex-col gap-4 pt-2" wire:click.stop>
            <fieldset class="m-0 flex w-full flex-col gap-4 border-0 p-0">
                <legend class="sr-only">{{ __('modals/absence/edit-absence.title') }}</legend>
            <div class="grid w-full grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="flex min-w-0 flex-col gap-1">
                    <x-forms.input
                        wire:model.live="form.date"
                        :required="true"
                        name="date"
                        type="date"
                        :label="__('modals/absence/edit-absence.date')" />
                    @error('form.date')
                        <span class="font-spaceGrotesk text-sm font-semibold text-input-error">{{ $message }}</span>
                    @enderror
                </div>
                <div class="flex min-w-0 flex-col gap-1">
                    <x-forms.select
                        wire:model.live="form.justification"
                        :name="'justification'"
                        :label="__('modals/absence/edit-absence.reason')"
                        :options="AbsenceJustification::cases()"
                        :required="true">
                        @error('form.justification')
                            <span class="font-spaceGrotesk text-sm font-semibold text-input-error">{{ $message }}</span>
                        @enderror
                    </x-forms.select>
                </div>
            </div>
            </fieldset>

            <div class="flex w-full flex-col-reverse gap-3 pt-2 sm:flex-row sm:justify-between sm:pt-4">
                <button
                    type="button"
                    wire:click="dispatch('close_modal')"
                    class="cta-secondary w-full sm:w-auto">
                    {{ __('modals/absence/edit-absence.cancel') }}
                </button>
                <x-forms.submit>
                    {{ __('modals/absence/edit-absence.save') }}
                </x-forms.submit>
            </div>
        </form>

    </x-layout.head-modal>
</div>
