<?php

use App\Models\ScrimGame;
use Livewire\Component;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

new class extends Component
{
    public ScrimGame $scrimGame;

    public function mount(int $model_id): void
    {
        $this->scrimGame = ScrimGame::query()
            ->with('scrim')
            ->findOrFail($model_id);

        abort_unless(
            $this->scrimGame->scrim->team_id === currentTeam()->id,
            403,
        );
    }

    public function closeModal(): void
    {
        $this->dispatch('close_modal');
    }

    public function deleteGame(): void
    {
        abort_unless(
            $this->scrimGame->scrim->team_id === currentTeam()->id,
            403,
        );
        if (Gate::denies('manageTeam', User::class)) {
            $this->dispatch('toast', [
                'title' => __('policies/scrim.error_title'),
                'message' => __('policies/scrim.error_delete_game'),
                'type' => 'error',
            ]);
            return;
        }

        $this->scrimGame->delete();

        $this->dispatch('close_modal');
        $this->dispatch('refresh_scrim');
        $this->dispatch('toast', [
            'title' => __('modals/scrims/games/delete-game.success_title'),
            'message' => __('modals/scrims/games/delete-game.success_message'),
            'type' => 'trash',
        ]);
    }
};
?>

<div>
    <x-layout.head-modal
        :title="__('modals/scrims/games/delete-game.title') . ' • ' . $this->scrimGame->title"
        :destroy="true"
    >
        <form wire:submit.prevent="deleteGame" class="flex flex-col items-center gap-6 pt-2 text-center">
            <div
                class="flex size-14 shrink-0 items-center justify-center rounded-none bg-red-950/40 ring-1 ring-red-900/60"
                aria-hidden="true">
                <flux:icon name="trash" class="size-8 text-red-700/90" />
            </div>

            <div class="flex max-w-sm flex-col gap-2">
                <p class="text-2xl font-bold text-text-primary">
                    {{ __('modals/scrims/games/delete-game.body_heading') }}
                </p>
                <p class="text-base font-normal text-text-secondary">
                    {{ __('modals/scrims/games/delete-game.body_legend') }}
                </p>
            </div>

            <div class="flex w-full max-w-md justify-center gap-3 sm:justify-between">
                <button
                    wire:click.prevent="closeModal"
                    type="button"
                    title="{{ __('modals/scrims/games/delete-game.cancel') }}"
                    class="cta-secondary">
                    {{ __('modals/scrims/games/delete-game.cancel') }}
                </button>
                <button
                    type="submit"
                    title="{{ __('modals/scrims/games/delete-game.delete') }}"
                    class="cursor-pointer rounded-none border border-red-900 bg-red-950/70 px-4 py-2 font-bold text-white transition-colors hover:bg-red-900/90">
                    {{ __('modals/scrims/games/delete-game.delete') }}
                </button>
            </div>
        </form>
    </x-layout.head-modal>
</div>
