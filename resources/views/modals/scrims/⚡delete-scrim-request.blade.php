<?php

use App\Models\ScrimRequest;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public ScrimRequest $scrimRequest;
    public function mount($model_id): void
    {
        $this->scrimRequest = ScrimRequest::with(['requesterTeam', 'receiverTeam'])
            ->findOrFail($model_id);

        $teamId = currentTeam()?->id;
        abort_unless(
            $teamId !== null
                && (
                    $teamId === $this->scrimRequest->requester_team_id
                    || $teamId === $this->scrimRequest->receiver_team_id
                ),
            403,
        );
    }

    public function closeModal(): void
    {
        $this->dispatch('close_modal');
    }

    public function deleteScrimRequest(): void
    {
        $teamId = currentTeam()?->id;
        abort_unless(
            $teamId !== null
                && (
                    $teamId === $this->scrimRequest->requester_team_id
                    || $teamId === $this->scrimRequest->receiver_team_id
                ),
            403,
        );

        $this->scrimRequest->delete();

        $this->dispatch('close_modal');
        $this->dispatch('refresh_scrims');
        $this->dispatch('toast', [
            'title' => __('modals/scrims/delete-scrim-request.success_title'),
            'message' => __('modals/scrims/delete-scrim-request.success_message'),
            'type' => 'trash',
        ]);
    }
};
?>

<div>
    <x-layout.head-modal
        :width="'2xl'"
        :title="__('modals/scrims/delete-scrim-request.title') . ' • ' . $this->scrimRequest->receiverTeam->name"
        :destroy="true"
    >
        <form wire:submit.prevent="deleteScrimRequest" class="flex flex-col items-center gap-6 pt-2 text-center">
            <div
                class="flex size-14 shrink-0 items-center justify-center rounded-none bg-red-950/40 ring-1 ring-red-900/60"
                aria-hidden="true">
                <flux:icon name="trash" class="size-8 text-red-700/90" />
            </div>

            <div class="flex flex-col gap-2">
                <p class="text-2xl font-bold text-text-primary">
                    {{ __('modals/scrims/delete-scrim-request.body_heading') }}
                </p>
                <p class="text-base font-normal text-text-secondary">
                    {{ __('modals/scrims/delete-scrim-request.body_legend') }}
                </p>
            </div>

            <div class="flex w-full  justify-center gap-3 sm:justify-between">
                <button
                    wire:click.prevent="closeModal"
                    type="button"
                    title="{{ __('modals/scrims/delete-scrim-request.cancel') }}"
                    class="cta-secondary">
                    {{ __('modals/scrims/delete-scrim-request.cancel') }}
                </button>
                <button
                    type="submit"
                    title="{{ __('modals/scrims/delete-scrim-request.delete') }}"
                    class="cursor-pointer rounded-none border border-red-900 bg-red-950/70 px-4 py-2 font-bold text-white transition-colors hover:bg-red-900/90">
                    {{ __('modals/scrims/delete-scrim-request.delete') }}
                </button>
            </div>
        </form>
    </x-layout.head-modal>
</div>
