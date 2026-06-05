<?php

use Livewire\Attributes\Computed;
use Livewire\Component;
use App\Enums\RoleInTeam;
use App\Models\TeamMember;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

new class extends Component
{
    public TeamMember $member;

    public function mount($model_id): void
    {
        abort_unless(Gate::allows('manageTeam', User::class), 403);

        $this->member = TeamMember::query()
            ->where('team_id', currentTeam()->id)
            ->with(['user.riotProfile'])
            ->findOrFail($model_id);
    }

    public function closeModal(): void
    {
        $this->dispatch('close_modal');
    }

    public function promoteToStarter(): void
    {
        if (Gate::denies('manageTeam', User::class)) {
            return;
        }

        DB::transaction(function () {
            $this->member->update([
                'is_starter' => true,
            ]);
            $this->member->team->averageEloScore();
        });

        $this->dispatch('close_modal');
        $this->dispatch('refresh_roster');
        $this->dispatch('toast', [
            'title' => __('modals/promote-to-starter.success_title'),
            'message' => __('modals/promote-to-starter.success_message'),
            'type' => 'check',
        ]);
    }

    #[Computed]
    public function existingStarterRoleInGame(): ?TeamMember
    {
        if ($this->member->roleInTeam !== RoleInTeam::PLAYER) {
            return null;
        }

        if ($this->member->is_starter) {
            return null;
        }

        return TeamMember::query()
            ->where('team_id', $this->member->team_id)
            ->where('roleInGame', $this->member->roleInGame)
            ->where('is_starter', true)
            ->whereKeyNot($this->member->id)
            ->with(['user.riotProfile'])
            ->first();
    }
};
?>

<div class="w-full">
    <x-layout.head-modal :width="'3xl'" :title="__('modals/promote-to-starter.title').' '.$this->member->user->username">

        <form wire:submit.prevent="promoteToStarter" class="flex w-full flex-col gap-6 pt-2">
            <div class="flex w-full flex-col gap-3">
                <div
                    class="mx-auto flex size-14 shrink-0 items-center justify-center rounded-none bg-gold/10 ring-1 ring-gold/40"
                    aria-hidden="true">
                    <flux:icon name="star" class="size-8 text-gold" />
                </div>

                <p class="text-2xl font-bold text-center text-text-primary">
                    {{ __('modals/promote-to-starter.body_heading') }}
                </p>
                <p class="text-base font-normal text-center text-text-secondary">
                    {{ __('modals/promote-to-starter.legend_form') }}
                </p>
            </div>


            @if ($this->existingStarterRoleInGame)
            <div class="flex w-full items-center gap-2 bg-red-900/60 p-2 text-left text-white">
                <flux:icon name="exclamation-triangle" variant="outline" class="size-12 shrink-0" aria-hidden="true" />
                <p class="text-sm sm:text-base">
                    {{ __('modals/promote-to-starter.conflict_notice', [
                            'existing_username' => $this->existingStarterRoleInGame->user->username,
                            'role_label' => $this->existingStarterRoleInGame->roleInGame->label(),
                            'member_username' => $this->member->user->username,
                        ]) }}
                </p>
            </div>
            @endif

            <div class="flex w-full flex-wrap justify-center gap-3 sm:justify-between">
                <button
                    wire:click.prevent="closeModal"
                    type="button"
                    title="{{ __('modals/promote-to-starter.cancel_button') }}"
                    class="cta-secondary">
                    {{ __('modals/promote-to-starter.cancel_button') }}
                </button>
                <button
                    type="submit"
                    title="{{ __('modals/promote-to-starter.confirm_button') }}"
                    class="cta-primary cursor-pointer">
                    {{ __('modals/promote-to-starter.confirm_button') }}
                </button>
            </div>
        </form>
    </x-layout.head-modal>
</div>