<?php

use Livewire\Component;
use App\Models\TeamMember;
use Illuminate\Support\Facades\DB;
use App\Enums\StatusInTeam;

new class extends Component
{
    public TeamMember $member;

    public function mount($model_id)
    {
        $this->member = TeamMember::findOrFail($model_id);
    }
    public function closeModal()
    {
        $this->dispatch('close_modal');
    }
    public function kickTeamMember()
    {
        $this->member->update([
            'status' => StatusInTeam::REJECTED,
        ]);
        
        $this->dispatch('close_modal');
        $this->dispatch('refresh_roster');
        $this->dispatch('toast', [
            'title' => __('modals/kick-team-member.success_title'),
            'message' => __('modals/kick-team-member.success_message'),
            'type' => 'trash',
        ]);
    }
};
?>

<div>
    <x-layout.head-modal :title="__('modals/kick-team-member.title') . ' ' . $this->member->user->username" :destroy="true">

        <form wire:submit.prevent="kickTeamMember" class="flex flex-col items-center gap-6 pt-2 text-center">
            <div
                class="flex size-14 shrink-0 items-center justify-center rounded-none bg-red-950/40 ring-1 ring-red-900/60"
                aria-hidden="true">
                <flux:icon name="trash" class="size-8 text-red-700/90" />
            </div>

            <div class="flex max-w-sm flex-col gap-2">
                <p class="text-2xl font-bold text-text-primary">
                    {{ __('modals/kick-team-member.body_heading') }}
                </p>
                <p class="text-base font-normal text-text-secondary">
                    {{ __('modals/kick-team-member.legend_form') }}
                </p>
            </div>

            <div class="flex w-full max-w-md justify-center gap-3 sm:justify-between">
                <button
                    wire:click.prevent="closeModal"
                    type="button"
                    title="{{ __('modals/kick-team-member.cancel_button') }}"
                    class="cta-secondary">
                    {{ __('modals/kick-team-member.cancel_button') }}
                </button>
                <button
                    type="submit"
                    title="{{ __('modals/kick-team-member.confirm_button') }}"
                    class="cursor-pointer rounded-none border border-red-900 bg-red-950/70 px-4 py-2 font-bold text-white transition-colors hover:bg-red-900/90">
                    {{ __('modals/kick-team-member.confirm_button') }}
                </button>
            </div>
        </form>
    </x-layout.head-modal>
</div>