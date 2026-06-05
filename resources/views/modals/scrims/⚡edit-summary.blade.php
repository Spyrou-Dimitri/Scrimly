<?php

use App\Enums\StatusScrim;
use App\Models\Scrim;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

new class extends Component
{
    public Scrim $scrim;

    public string $summary = '';

    public function mount(int $model_id): void
    {
        $this->scrim = Scrim::query()
            ->with('opponentTeam')
            ->findOrFail($model_id);

        abort_unless(
            $this->scrim->team_id === currentTeam()->id,
            403,
        );

        abort_unless(
            $this->scrim->status !== StatusScrim::SCHEDULED,
            403,
        );

        $this->summary = $this->scrim->summary ?? '';
    }

    public function closeModal(): void
    {
        $this->dispatch('close_modal');
    }

    public function saveSummary(): void
    {
        abort_unless(
            $this->scrim->team_id === currentTeam()->id,
            403,
        );

        abort_unless(
            $this->scrim->status !== StatusScrim::SCHEDULED,
            403,
        );

        if (Gate::denies('manageTeam', User::class)) {
            $this->dispatch('toast', [
                'title' => __('policies/scrim.error_title'),
                'message' => __('policies/scrim.error_edit_summary'),
                'type' => 'error',
            ]);
            $this->dispatch('close_modal');

            return;
        }

        $validated = $this->validate([
            'summary' => ['nullable', 'string', 'max:1000'],
        ]);

        $summary = filled($validated['summary'])
            ? trim($validated['summary'])
            : null;

        $this->scrim->update(['summary' => $summary]);

        $this->dispatch('close_modal');
        $this->dispatch('refresh_scrim');
        $this->dispatch('toast', [
            'title' => __('modals/scrims/edit-summary.success_title'),
            'message' => __('modals/scrims/edit-summary.success_message'),
            'type' => 'check',
        ]);
    }
};
?>

<div class="w-full">
    <x-layout.head-modal
        :width="'2xl'"
        :title="__('modals/scrims/edit-summary.title') . ' • ' . ($this->scrim->opponentTeam?->name ?? __('pages/scrims/show.opponent_unknown'))">
        <form wire:submit.prevent="saveSummary" class="flex w-full flex-col gap-6 pt-2">
            <fieldset class="m-0 flex w-full flex-col gap-6 border-0 p-0">
                <legend class="sr-only">{{ __('modals/scrims/edit-summary.title') }}</legend>
            <x-forms.textarea
                wire:model="summary"
                name="scrim-summary-edit"
                :label="__('modals/scrims/edit-summary.summary')"
                :placeholder="__('modals/scrims/edit-summary.summary_placeholder')"
                :rows="8">
                @error('summary')
                    <p class="text-red-500">{{ $message }}</p>
                @enderror
            </x-forms.textarea>
            </fieldset>

            <div class="flex w-full flex-wrap justify-center gap-3 sm:justify-between">
                <button
                    wire:click.prevent="closeModal"
                    type="button"
                    title="{{ __('modals/scrims/edit-summary.cancel') }}"
                    class="cta-secondary">
                    {{ __('modals/scrims/edit-summary.cancel') }}
                </button>
                <button
                    type="submit"
                    title="{{ __('modals/scrims/edit-summary.save') }}"
                    class="cta-primary cursor-pointer">
                    {{ __('modals/scrims/edit-summary.save') }}
                </button>
            </div>
        </form>
    </x-layout.head-modal>
</div>
