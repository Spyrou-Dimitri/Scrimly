<?php

use Livewire\Component;
use App\Models\User;
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
        DB::transaction(function () {

            $this->member->update([
                'status' => StatusInTeam::REJECTED,
            ]);

            if ($this->member->user->current_team_id === $this->member->team_id) {
                $this->member->user->update([
                    'current_team_id' => null,
                ]);
            }
        });
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

        <form wire:submit.prevent="kickTeamMember" class="flex flex-col gap-4">
            <legend class="text-xl font-bold">
                {{ __('modals/kick-team-member.legend_form') }}
            </legend>
            <div class="flex justify-between gap-2">
                <button wire:click="closeModal" class="cta-secondary cursor-pointer" type="button" title="{{ __('modals/kick-team-member.cancel_button') }}">
                    {{ __('modals/kick-team-member.cancel_button') }}
                </button>
                <button class="py-2 px-4 bg-red-700/60 hover:bg-red-700 transition-all duration-150 text-whites border border-red-900 font-bold cursor-pointer" type="submit" title="{{ __('modals/kick-team-member.confirm_button') }}">
                    {{ __('modals/kick-team-member.confirm_button') }}
                </button>
            </div>



        </form>
    </x-layout.head-modal>
</div>