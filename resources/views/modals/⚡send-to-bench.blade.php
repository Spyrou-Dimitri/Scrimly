<?php

use Livewire\Component;
use App\Models\TeamMember;
use Illuminate\Support\Facades\DB;

new class extends Component
{
    public TeamMember $member;

    public function mount($model_id): void
    {
        $this->member = TeamMember::query()
            ->with(['user.riotProfile'])
            ->findOrFail($model_id);
    }

    public function closeModal(): void
    {
        $this->dispatch('close_modal');
    }

    public function sendToBench(): void
    {
        DB::transaction(function () {
            $this->member->update([
                'is_starter' => false,
            ]);
            $this->member->team->averageEloScore();
        });

        $this->dispatch('close_modal');
        $this->dispatch('refresh_roster');
        $this->dispatch('toast', [
            'title' => __('modals/send-to-bench.success_title'),
            'message' => __('modals/send-to-bench.success_message'),
            'type' => 'check',
        ]);
    }
};
?>

<div class="w-full">
    <x-layout.head-modal :width="'xl'" :title="__('modals/send-to-bench.title').' '.$this->member->user->username">

        <form wire:submit.prevent="sendToBench" class="flex w-full flex-col gap-6 pt-2">
            <div class="flex w-full flex-col gap-3">
                <div
                    class="mx-auto flex size-14 shrink-0 items-center justify-center rounded-none bg-zinc-800/80 ring-1 ring-zinc-600/60"
                    aria-hidden="true">
                    <flux:icon name="arrow-down-circle" class="size-8 text-text-secondary" />
                </div>
                <p class="text-2xl font-bold text-center text-text-primary">
                    {{ __('modals/send-to-bench.body_heading') }}
                </p>
                <p class="text-base font-normal text-center text-text-secondary">
                    {{ __('modals/send-to-bench.legend_form') }}
                </p>
            </div>


            <div class="flex w-full flex-wrap justify-center gap-3 sm:justify-between">
                <button
                    wire:click.prevent="closeModal"
                    type="button"
                    title="{{ __('modals/send-to-bench.cancel_button') }}"
                    class="cta-secondary">
                    {{ __('modals/send-to-bench.cancel_button') }}
                </button>
                <button
                    type="submit"
                    title="{{ __('modals/send-to-bench.confirm_button') }}"
                    class="cta-primary cursor-pointer">
                    {{ __('modals/send-to-bench.confirm_button') }}
                </button>
            </div>
        </form>
    </x-layout.head-modal>
</div>