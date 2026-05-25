<?php

use App\Models\Absence;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

new class extends Component
{
    public Absence $absence;

    public function mount(int $model_id): void
    {
        $this->absence = Absence::query()
            ->whereKey($model_id)
            ->whereHas('teamMember', fn ($query) => $query->where('team_id', currentTeam()->id))
            ->firstOrFail();

        abort_unless(Gate::allows('manageAvailability', $this->absence->teamMember), 403);
    }

    public function closeModal()
    {
        $this->dispatch('close_modal');
    }

    public function deleteAbsence()
    {
        abort_unless(Gate::allows('manageAvailability', $this->absence->teamMember), 403);

        $this->absence->delete();
        $this->dispatch('close_modal');
        $this->dispatch('refresh_absences');
        $this->dispatch('toast', [
            'title' => __('modals/absence/delete-absence.success_title'),
            'message' => __('modals/absence/delete-absence.success_message'),
            'type' => 'trash',
        ]);
    }
};
?>

<div>
    <x-layout.head-modal :title="__('modals/absence/delete-absence.title')" :destroy="true">
        <form wire:submit.prevent="deleteAbsence" class="flex flex-col items-center gap-6 pt-2 text-center">
            <div
                class="flex size-14 shrink-0 items-center justify-center rounded-none bg-red-950/40 ring-1 ring-red-900/60"
                aria-hidden="true">
                <flux:icon name="trash" class="size-8 text-red-700/90" />
            </div>

            <div class="flex max-w-sm flex-col gap-2">
                <p class="text-2xl font-bold text-text-primary">
                    {{ __('modals/absence/delete-absence.message') }}
                </p>
                <p class="text-base font-normal text-text-secondary">
                    {{ __('modals/absence/delete-absence.message') }}
                </p>
            </div>

            <div class="flex w-full max-w-md gap-3 justify-between">
                <button
                    wire:click.prevent="closeModal"
                    type="button"
                    title="{{ __('modals/absence/delete-absence.cancel') }}"
                    class="cta-secondary">
                    {{ __('modals/absence/delete-absence.cancel') }}
                </button>
                <button
                    type="submit"
                    title="{{ __('modals/absence/delete-absence.delete') }}"
                    class="cursor-pointer rounded-none border border-red-900 bg-red-950/70 px-4 py-2 font-bold text-white transition-colors hover:bg-red-900/90">
                    {{ __('modals/absence/delete-absence.delete') }}
                </button>
            </div>
        </form>
    </x-layout.head-modal>
</div>
