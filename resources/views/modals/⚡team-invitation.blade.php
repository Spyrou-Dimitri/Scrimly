<?php

use App\Enums\RoleInTeam;
use App\Models\TeamInvitation;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use App\Enums\StatusInvitation;
use App\Enums\StatusInTeam;
use App\Models\TeamMember;
use Illuminate\Support\Facades\DB;

new class extends Component
{
    public TeamInvitation $invitation;

    public function mount($model_id): void
    {
        $this->invitation = TeamInvitation::query()
            ->where('user_id', Auth::id())
            ->with('team')
            ->findOrFail($model_id);
    }
    public function refuseInvitation(): void
    {
        DB::transaction(function () {
            $this->invitation->update([
                'status' => StatusInvitation::REJECTED,
            ]);
        });
        $this->dispatch('close_modal');
        $this->dispatch('toast', [
            'title' => __('toasts/toasts.invitation_refused'),
            'message' => __('toasts/toasts.invitation_refused_message', ['team' => $this->invitation->team->name]),
            'type' => 'error',
        ]);
        $this->dispatch('refresh_invitations');
    }

    public function acceptInvitation(): void
    {
        DB::transaction(function () {
            $this->invitation->update([
                'status' => StatusInvitation::ACCEPTED,
            ]);
            $hasStarterForRole = TeamMember::query()
                ->where('team_id', $this->invitation->team_id)
                ->where('roleInTeam', $this->invitation->roleInTeam)
                ->where('roleInGame', $this->invitation->roleInGame)
                ->where('is_starter', true)
                ->where('status', StatusInTeam::ACCEPTED)
                ->exists();

            TeamMember::updateOrCreate([
                'team_id' => $this->invitation->team_id,
                'user_id' => $this->invitation->user_id,
            ], [
                'roleInTeam' => $this->invitation->roleInTeam,
                'roleInGame' => $this->invitation->roleInGame,
                'status' => StatusInTeam::ACCEPTED,
                'is_starter' => ! $hasStarterForRole,
            ]);

            if (! $hasStarterForRole) {
                $this->invitation->team->averageEloScore();
            }
        });
        $this->dispatch('close_modal');
        $this->dispatch('toast', [
            'title' => __('toasts/toasts.invitation_accepted'),
            'message' => __('toasts/toasts.invitation_accepted_message', ['team' => $this->invitation->team->name]),
            'type' => 'success',
        ]);
        $this->dispatch('refresh_invitations');
    }
};
?>

<div>
    <x-layout.head-modal :width="'3xl'" :title="__('modals/team-invitation.title') . ' ' . $this->invitation->team->name">
        <div class="flex flex-col gap-6">
            <div class="flex flex-col items-center gap-4 sm:flex-row sm:items-center sm:gap-6">
                <x-team-logo
                    :team="$this->invitation->team"
                    preset="pending-row"
                    class="size-20 shrink-0 object-contain sm:size-24" />
                <div class="flex flex-col items-center gap-2 text-center sm:items-start sm:text-left">
                    <h3 class="text-2xl font-bold text-gold">{{ $this->invitation->team->name }}</h3>
                    <div class="flex flex-wrap items-center justify-center gap-2 sm:justify-start">
                        <span class="inline-flex items-center bg-tag-server/20 px-3 py-1 text-xs font-medium text-tag-server">
                            {{ $this->invitation->team->server->label() }}
                        </span>
                        <span class="inline-flex items-center bg-tag-language/20 px-3 py-1 text-xs font-medium text-tag-language">
                            {{ $this->invitation->team->language->label() }}
                        </span>
                        <span class="inline-flex items-center {{ $this->invitation->team->goal->macaron() }} px-3 py-1 text-xs font-medium">
                            {{ $this->invitation->team->goal->label() }}
                        </span>
                    </div>
                </div>
            </div>

            <dl class="grid grid-cols-1 gap-4 border-y-2 border-input-border py-6 md:grid-cols-2">
                <div class="flex flex-col items-center gap-2 text-center md:items-start md:text-left">
                    <dt class="text-text-gray">{{ __('modals/team-invitation.proposed_role') }}</dt>
                    <dd class="flex items-center gap-2 text-xl font-bold text-white">
                        @if ($invitation->roleInTeam === RoleInTeam::COACH || $invitation->roleInTeam === RoleInTeam::STAFF)
                        <span>{{ $invitation->roleInTeam->label() }}</span>
                        @else
                        <img src="{{ asset($invitation->roleInGame->icon()) }}" class="size-7" alt="{{ $invitation->roleInGame->label() }}">
                        <span>{{ $invitation->roleInGame->label() }}</span>
                        @endif
                    </dd>
                </div>
            </dl>

            <section class="flex flex-col gap-2">
                <h4 class="text-2xl font-bold text-gold">
                    {{ __('modals/team-invitation.motivation') }}
                </h4>
                <p class="text-white">
                    @if (filled($invitation->motivation))
                    {{ $invitation->motivation }}
                    @else
                    <span class="italic text-text-secondary">
                        {{ __('modals/team-invitation.no_motivation') }}
                    </span>
                    @endif
                </p>
            </section>

            <div class="flex flex-col-reverse justify-between gap-3 sm:flex-row">
                <x-destructive type="button" wire:click="refuseInvitation">
                    {{ __('modals/team-invitation.refuse') }}
                </x-destructive>
                <x-accept type="button" wire:click="acceptInvitation">
                    {{ __('modals/team-invitation.accept') }}
                </x-accept>
            </div>
        </div>
    </x-layout.head-modal>
</div>