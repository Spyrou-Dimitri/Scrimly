<?php

use App\Enums\StatusScrim;
use App\Models\Scrim;
use Livewire\Component;

new class extends Component
{
    public Scrim $scrim;
    public int $gamesPlayed = 0;
    public int $gamesPlanned = 0;
    public function mount(int $model_id): void
    {
        $this->scrim = Scrim::query()
            ->with(['opponentTeam', 'scrimGames'])
            ->findOrFail($model_id);

        abort_unless(
            $this->scrim->team_id === currentTeam()->id,
            403,
        );

        $this->gamesPlayed = $this->scrim->scrimGames->count();
        $this->gamesPlanned = $this->scrim->number_of_games;
    }

    public function closeModal(): void
    {
        $this->dispatch('close_modal');
    }

    public function markScrimAsCompleted(): void
    {
        $this->scrim->update(['status' => StatusScrim::COMPLETED]);
        $this->dispatch('close_modal');
        $this->dispatch('refresh_scrim');
        $this->dispatch('toast', [
            'title' => __('modals/scrims/handle-create-game.success_title'),
            'message' => __('modals/scrims/handle-create-game.success_message'),
            'type' => 'check',
        ]);
    }

    public function createGameAnyway(): void
    {
        redirect()->route('scrims.games.create', ['slug' => $this->scrim->team->slug, 'id' => $this->scrim->id]);
    }
};
?>

<div class="w-full">
    <x-layout.head-modal
        :width="'2xl'"
        :title="__('modals/scrims/handle-create-game.title') . ' • ' . ($this->scrim->opponentTeam?->name ?? __('pages/scrims/show.opponent_unknown'))"
    >
        <div class="flex w-full flex-col gap-6 pt-2">
            <div class="flex w-full flex-col gap-3">
                <div
                    class="mx-auto flex size-14 shrink-0 items-center justify-center rounded-none bg-gold/10 ring-1 ring-gold/40"
                    aria-hidden="true">
                    <flux:icon name="exclamation-triangle" class="size-8 text-gold" />
                </div>

                <p class="text-center text-2xl font-bold text-text-primary">
                    {{ __('modals/scrims/handle-create-game.body_heading') }}
                </p>
                <p class="text-center text-base font-normal text-text-secondary">
                    {{ __('modals/scrims/handle-create-game.body_legend', [
                        'played' => $gamesPlayed,
                        'planned' => $gamesPlanned,
                    ]) }}
                </p>
            </div>

            <div class="flex w-full flex-wrap justify-center gap-3 sm:justify-between">
                <button
                    wire:click="markScrimAsCompleted"
                    type="button"
                    title="{{ __('modals/scrims/handle-create-game.finish_scrim') }}"
                    class="cta-secondary">
                    {{ __('modals/scrims/handle-create-game.finish_scrim') }}
                </button>
                <button
                    wire:click="createGameAnyway"
                    type="button"
                    title="{{ __('modals/scrims/handle-create-game.create_game') }}"
                    class="cta-primary cursor-pointer">
                    {{ __('modals/scrims/handle-create-game.create_game') }}
                </button>
            </div>
        </div>
    </x-layout.head-modal>
</div>
