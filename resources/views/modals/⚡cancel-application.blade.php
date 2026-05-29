<?php

use App\Models\TeamApplication;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component
{
    public TeamApplication $application;

    public function mount(int $model_id): void
    {
        $this->application = TeamApplication::query()
            ->where('user_id', Auth::id())
            ->with('team')
            ->findOrFail($model_id);
    }

    public function closeModal(): void
    {
        $this->dispatch('close_modal');
    }

    public function cancelApplication(): void
    {
        $this->application->delete();

        $this->dispatch('close_modal');
        $this->dispatch('refresh_applications');
        $this->dispatch('toast', [
            'title' => __('toasts/toasts.application_cancelled'),
            'message' => __('toasts/toasts.application_cancelled_message', ['team' => $this->application->team->name]),
            'type' => 'trash',
        ]);
    }
};
?>

<div>
    <x-layout.head-modal
        :title="__('modals/cancel-application.title') . ' ' . $this->application->team->name"
        :destroy="true">
        <form wire:submit.prevent="cancelApplication" class="flex flex-col items-center gap-6 pt-2 text-center">
            <div
                class="flex size-14 shrink-0 items-center justify-center rounded-none bg-red-950/40 ring-1 ring-red-900/60"
                aria-hidden="true">
                <flux:icon name="trash" class="size-8 text-red-700/90" />
            </div>

            <div class="flex max-w-sm flex-col gap-2">
                <p class="text-2xl font-bold text-text-primary">
                    {{ __('modals/cancel-application.body_heading') }}
                </p>
                <p class="text-base font-normal text-text-secondary">
                    {{ __('modals/cancel-application.legend_form', ['team' => $this->application->team->name]) }}
                </p>
            </div>

            <div class="flex w-full max-w-md justify-center gap-3 sm:justify-between">
                <button
                    wire:click.prevent="closeModal"
                    type="button"
                    title="{{ __('modals/cancel-application.cancel_button') }}"
                    class="cta-secondary">
                    {{ __('modals/cancel-application.cancel_button') }}
                </button>
                <button
                    type="submit"
                    title="{{ __('modals/cancel-application.confirm_button') }}"
                    class="cursor-pointer rounded-none border border-red-900 bg-red-950/70 px-4 py-2 font-bold text-white transition-colors hover:bg-red-900/90">
                    {{ __('modals/cancel-application.confirm_button') }}
                </button>
            </div>
        </form>
    </x-layout.head-modal>
</div>
