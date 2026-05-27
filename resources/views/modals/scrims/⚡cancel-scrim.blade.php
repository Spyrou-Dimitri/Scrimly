<?php

use App\Enums\StatusScrim;
use App\Models\Scrim;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

new class extends Component
{
    public Scrim $scrim;

    public function mount(int $model_id): void
    {
        $this->scrim = Scrim::query()
            ->with('opponentTeam')
            ->findOrFail($model_id);

        abort_unless(
            $this->scrim->team_id === currentTeam()->id,
            403,
        );
    }

    public function closeModal(): void
    {
        $this->dispatch('close_modal');
    }

    public function cancelScrim(): void
    {
        abort_unless(
            $this->scrim->team_id === currentTeam()->id,
            403,
        );

        abort_unless(
            $this->scrim->status === StatusScrim::SCHEDULED,
            403,
        );

        if (Gate::denies('manageTeam', User::class)) {
            $this->dispatch('toast', [
                'title' => __('policies/scrim.error_title'),
                'message' => __('policies/scrim.error_cancel_scrim'),
                'type' => 'error',
            ]);
            $this->dispatch('close_modal');

            return;
        }

        $this->scrim->update(['status' => StatusScrim::CANCELLED]);

        $this->dispatch('close_modal');
        $this->dispatch('refresh_scrim');
        $this->dispatch('refresh_scrims');
        $this->dispatch('toast', [
            'title' => __('modals/scrims/cancel-scrim.success_title'),
            'message' => __('modals/scrims/cancel-scrim.success_message'),
            'type' => 'check',
        ]);
    }
};
?>

<div class="w-full">
    <x-layout.head-modal
        :width="'xl'"
        :title="__('modals/scrims/cancel-scrim.title') . ' • ' . ($this->scrim->opponentTeam?->name ?? __('pages/scrims/show.opponent_unknown'))"
        :destroy="true"
    >
        <form wire:submit.prevent="cancelScrim" class="flex w-full flex-col items-center gap-6 pt-2 text-center">
            <div
                class="flex size-14 shrink-0 items-center justify-center rounded-none bg-red-950/40 ring-1 ring-red-900/60"
                aria-hidden="true">
                <flux:icon name="x-circle" class="size-8 text-red-700/90" />
            </div>

            <div class="flex flex-col gap-2">
                <p class="text-2xl font-bold text-text-primary">
                    {{ __('modals/scrims/cancel-scrim.body_heading') }}
                </p>
                <p class="text-base font-normal text-text-secondary">
                    {{ __('modals/scrims/cancel-scrim.body_legend') }}
                </p>
            </div>

            <div class="flex w-full flex-wrap justify-center gap-3 sm:justify-between">
                <button
                    wire:click.prevent="closeModal"
                    type="button"
                    title="{{ __('modals/scrims/cancel-scrim.cancel') }}"
                    class="cta-secondary">
                    {{ __('modals/scrims/cancel-scrim.cancel') }}
                </button>
                <button
                    type="submit"
                    title="{{ __('modals/scrims/cancel-scrim.confirm') }}"
                    class="cursor-pointer rounded-none border border-red-900 bg-red-950/70 px-4 py-2 font-bold text-white transition-colors hover:bg-red-900/90">
                    {{ __('modals/scrims/cancel-scrim.confirm') }}
                </button>
            </div>
        </form>
    </x-layout.head-modal>
</div>
