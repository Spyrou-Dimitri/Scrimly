<?php

use App\Models\Scrim;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

new class extends Component
{
    public Scrim $scrim;

    public function mount(int $model_id): void
    {
        $scrim = Scrim::with('opponentTeam')->findOrFail($model_id);

        abort_unless(
            $scrim->team_id === currentTeam()->id
                && Gate::allows('manageTeam', User::class),
            403,
        );

        $this->scrim = $scrim;
    }

    public function closeModal(): void
    {
        $this->dispatch('close_modal');
    }

    public function deleteScrim(): void
    {
        abort_unless(
            $this->scrim->team_id === currentTeam()->id
                && Gate::allows('manageTeam', User::class),
            403,
        );

        $this->scrim->delete();

        $this->dispatch('close_modal');
        $this->dispatch('refresh_scrims');
        $this->dispatch('toast', [
            'title' => __('modals/scrims/delete-scrim.success_title'),
            'message' => __('modals/scrims/delete-scrim.success_message'),
            'type' => 'trash',
        ]);
    }
};
?>

<div>
    <x-layout.head-modal
        :title="__('modals/scrims/delete-scrim.title') . ' • ' . ($this->scrim->opponentTeam?->name ?? __('pages/scrims/index.upcoming_opponent_unknown'))"
        :destroy="true"
    >
        <form wire:submit.prevent="deleteScrim" class="flex flex-col items-center gap-6 pt-2 text-center">
            <div
                class="flex size-14 shrink-0 items-center justify-center rounded-none bg-red-950/40 ring-1 ring-red-900/60"
                aria-hidden="true">
                <flux:icon name="trash" class="size-8 text-red-700/90" />
            </div>

            <div class="flex max-w-sm flex-col gap-2">
                <p class="text-2xl font-bold text-text-primary">
                    {{ __('modals/scrims/delete-scrim.body_heading') }}
                </p>
                <p class="text-base font-normal text-text-secondary">
                    {{ __('modals/scrims/delete-scrim.body_legend') }}
                </p>
            </div>

            <div class="flex w-full max-w-md justify-center gap-3 sm:justify-between">
                <button
                    wire:click.prevent="closeModal"
                    type="button"
                    title="{{ __('modals/scrims/delete-scrim.cancel') }}"
                    class="cta-secondary">
                    {{ __('modals/scrims/delete-scrim.cancel') }}
                </button>
                <button
                    type="submit"
                    title="{{ __('modals/scrims/delete-scrim.delete') }}"
                    class="cursor-pointer rounded-none border border-red-900 bg-red-950/70 px-4 py-2 font-bold text-white transition-colors hover:bg-red-900/90">
                    {{ __('modals/scrims/delete-scrim.delete') }}
                </button>
            </div>
        </form>
    </x-layout.head-modal>
</div>
