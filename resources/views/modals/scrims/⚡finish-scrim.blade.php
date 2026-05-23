<?php

use App\Enums\StatusScrim;
use App\Models\Scrim;
use Livewire\Component;
use Illuminate\Support\Facades\Gate;
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

    public function finishScrim(): void
    {
        abort_unless(
            $this->scrim->team_id === currentTeam()->id,
            403,
        );

        abort_unless(
            $this->scrim->status === StatusScrim::IN_PROGRESS,
            403,
        );
        if (Gate::denies('edit', Scrim::class)) {
            $this->dispatch('toast', [
                'title' => __('policies/scrim.error_title'),
                'message' => __('policies/scrim.error_finish_scrim'),
                'type' => 'error',
            ]);
            $this->dispatch('close_modal');
            return;
        }

        $this->scrim->update(['status' => StatusScrim::COMPLETED]);

        
        $this->dispatch('close_modal');
        $this->dispatch('refresh_scrim');
        $this->dispatch('toast', [
            'title' => __('modals/scrims/finish-scrim.success_title'),
            'message' => __('modals/scrims/finish-scrim.success_message'),
            'type' => 'check',
        ]);
    }
};
?>

<div class="w-full">
    <x-layout.head-modal
        :width="'2xl'"
        :title="__('modals/scrims/finish-scrim.title') . ' • ' . ($this->scrim->opponentTeam?->name ?? __('pages/scrims/show.opponent_unknown'))"
    >
        <form wire:submit.prevent="finishScrim" class="flex w-full flex-col gap-6 pt-2">
            <div class="flex w-full flex-col gap-3">
                <div
                    class="mx-auto flex size-14 shrink-0 items-center justify-center rounded-none bg-gold/10 ring-1 ring-gold/40"
                    aria-hidden="true">
                    <flux:icon name="check" class="size-8 text-gold" />
                </div>

                <p class="text-center text-2xl font-bold text-text-primary">
                    {{ __('modals/scrims/finish-scrim.body_heading') }}
                </p>
                <p class="text-center text-base font-normal text-text-secondary">
                    {{ __('modals/scrims/finish-scrim.body_legend') }}
                </p>
            </div>

            <div class="flex w-full flex-wrap justify-center gap-3 sm:justify-between">
                <button
                    wire:click.prevent="closeModal"
                    type="button"
                    title="{{ __('modals/scrims/finish-scrim.cancel') }}"
                    class="cta-secondary">
                    {{ __('modals/scrims/finish-scrim.cancel') }}
                </button>
                <button
                    type="submit"
                    title="{{ __('modals/scrims/finish-scrim.confirm') }}"
                    class="cta-primary cursor-pointer">
                    {{ __('modals/scrims/finish-scrim.confirm') }}
                </button>
            </div>
        </form>
    </x-layout.head-modal>
</div>
