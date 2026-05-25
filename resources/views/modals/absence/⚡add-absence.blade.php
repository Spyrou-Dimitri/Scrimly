<?php

use App\Enums\AbsenceJustification;
use App\Livewire\Forms\Absence\AddAbsenceForm;
use App\Models\TeamMember;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

new class extends Component
{

    public TeamMember $teamMember;

    public AddAbsenceForm $form;

    public function mount(int $model_id): void
    {
        $this->teamMember = TeamMember::query()
            ->whereKey($model_id)
            ->where('team_id', currentTeam()->id)
            ->firstOrFail();

        abort_unless(Gate::allows('manageAvailability', $this->teamMember), 403);
    }

    public function store(): void
    {
        abort_unless(Gate::allows('manageAvailability', $this->teamMember), 403);

        $this->form->store($this->teamMember->id);

        $this->dispatch('close_modal');
        $this->dispatch('refresh_absences');
        $this->dispatch('toast', [
            'title' => __('modals/absence/add-absence.success'),
            'message' => __('modals/absence/add-absence.success_message'),
            'type' => 'success',
        ]);
    }
};
?>

<div>
    <x-layout.head-modal :width="'xl'" :title="__('modals/absence/add-absence.title')">
        <form wire:submit.prevent="store" class="flex flex-col gap-4" wire:click.stop>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="flex flex-col gap-1">
                    <x-forms.input
                        wire:model="form.absenceDay"
                        :required="true"
                        name="absence-day"
                        type="date"
                        :label="__('modals/absence/add-absence.day')" />
                    @error('form.absenceDay')
                        <span class="font-spaceGrotesk text-input-error text-sm font-semibold">{{ $message }}</span>
                    @enderror
                </div>
                <div class="flex flex-col gap-1">
                    <x-forms.select
                        wire:model.live="form.justification"
                        :name="'justification'"
                        :label="__('modals/absence/add-absence.reason')"
                        :options="AbsenceJustification::cases()"
                        :required="true"
                        :disabled="__('modals/absence/add-absence.select_placeholder')" />
                    @error('form.justification')
                        <span class="font-spaceGrotesk text-input-error text-sm font-semibold">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="flex flex-col-reverse gap-3 pt-2 sm:flex-row sm:justify-between sm:pt-4">
                <button
                    type="button"
                    wire:click="dispatch('close_modal')"
                    class="cta-secondary w-full sm:w-auto">
                    {{ __('modals/absence/add-absence.cancel') }}
                </button>
                <x-forms.submit>
                    {{ __('modals/absence/add-absence.create') }}
                </x-forms.submit>
            </div>
        </form>
    </x-layout.head-modal>
</div>
