<?php

use App\Enums\RoleInTeam;
use App\Enums\StatusApplication;
use App\Enums\StatusInTeam;
use App\Enums\StatusInvitation;
use App\Models\TeamApplication;
use App\Models\TeamInvitation;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;


new #[Layout('layouts::choose_a_team')] class extends Component
{
    public User $currentUser;

    public Collection $teamMembers;

    public Collection $pendingInvitations;

    public Collection $sentApplications;

    public function mount(): void
    {
        $this->currentUser = Auth::user();

        if ($this->currentUser->current_team_id !== null) {
            $this->currentUser->update(['current_team_id' => null]);
            $this->currentUser->refresh();
        }

        $this->loadData();
    }

    #[On('refresh_invitations')]
    public function refreshInvitations(): void
    {
        $this->loadData();
    }

    #[On('refresh_applications')]
    public function refreshApplications(): void
    {
        $this->loadData();
    }

    public function selectTeam(int $teamId): void
    {
        $team = $this->currentUser->teams->firstWhere('id', $teamId);

        if (! $team) {
            return;
        }

        $this->currentUser->current_team_id = $team->id;
        $this->currentUser->save();

        Auth::user()->current_team_id = $team->id;

        $this->redirect(route('roster.index', ['slug' => $team->slug]));
    }

    public function openTeamInvitationModal(int $invitationId): void
    {
        $invitation = TeamInvitation::query()
            ->where('user_id', $this->currentUser->id)
            ->where('status', StatusInvitation::PENDING)
            ->find($invitationId);

        if (! $invitation) {
            return;
        }

        $this->dispatch('open_modal', [
            'form' => 'team-invitation',
            'model_id' => $invitationId,
        ]);
    }

    public function openModalCancelApplication(int $applicationId): void
    {
        $this->dispatch('open_modal', [
            'form' => 'cancel-application',
            'model_id' => $applicationId,
        ]);
    }
    private function loadData(): void
    {
        $this->teamMembers = TeamMember::query()
            ->where('user_id', $this->currentUser->id)
            ->where('status', StatusInTeam::ACCEPTED)
            ->with('team')
            ->get();

        $this->pendingInvitations = TeamInvitation::query()
            ->where('user_id', $this->currentUser->id)
            ->where('status', StatusInvitation::PENDING)
            ->with('team')
            ->latest()
            ->get();

        $this->sentApplications = TeamApplication::query()
            ->where('user_id', $this->currentUser->id)
            ->with('team')
            ->latest()
            ->get();
    }
};
?>

<div class="flex w-full flex-col gap-10">
    <section class="flex w-full flex-col items-center justify-center gap-8 text-center">
        <div class="flex flex-col gap-4">
            @if ($currentUser->teams->count() === 0)
            <h2 class="text-[40px] font-bold leading-tight">
                {{ __('pages/team/index.onboarding_title') }}
                <span class="text-gold font-bold">{{ $currentUser->username }}</span>
            </h2>
            <p class="text-2xl text-text-secondary">
                {{ __('pages/team/index.onboarding_description') }}
            </p>
            @else
            <h2 class="text-[40px] font-bold leading-tight">
                {{ __('pages/team/index.title') }}
                <span class="text-gold font-bold">{{ $currentUser->username }}</span> !
            </h2>
            <p class="text-2xl text-text-secondary">
                {{ __('pages/team/index.description') }}
            </p>
            @endif
        </div>
        <div class="flex flex-col gap-4 sm:flex-row">
            <x-cta :href="route('team.create')" :title="__('pages/team/index.create_team_title')" :class="'primary'">
                {{ __('pages/team/index.create_team_cta') }}
            </x-cta>
            <x-cta :href="route('team.join')" :title="__('pages/team/index.join_team_title')" :class="'secondary'">
                {{ __('pages/team/index.join_team_cta') }}
            </x-cta>
        </div>
    </section>

    <section class="grid w-full grid-cols-1 items-start gap-6 lg:grid-cols-12">
        {{-- Mes équipes --}}
        <x-accordion
            :headingLevel="'h3'"
            :title="__('pages/team/index.my_teams_title')"
            :open="true"
            :count="$teamMembers->count()"
            :panelClass="'mt-6 flex flex-col gap-4'"
            class="lg:col-span-7">
            @if ($teamMembers->isEmpty())
            <li class="bg-bg-card p-6 text-center text-text-secondary">
                {{ __('pages/team/index.my_teams_empty') }}
            </li>
            @else
            @foreach ($teamMembers as $teamMember)
            <li wire:key="team-member-{{ $teamMember->id }}">
                <article
                    x-data
                    x-on:click="$el.querySelector('[data-team-link]')?.click()"
                    class="card-animated-border relative flex cursor-pointer items-center gap-4 border-l-2 border-gold bg-bg-card p-4 shadow-basic md:p-5">
                    <span class="card-animated-border-right-edge" aria-hidden="true"></span>
                    <div class="relative z-[1] flex w-full flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        <div class="flex items-center gap-4">
                            <div class="size-14 shrink-0 overflow-hidden sm:size-16">
                                <x-team-logo
                                    :team="$teamMember->team"
                                    preset="team-finder"
                                    class="size-full object-cover" />
                            </div>
                            <div class="min-w-0">
                                <h3 class="truncate text-xl font-bold text-white">{{ $teamMember->team->name }}</h3>
                                <p class="truncate text-sm text-text-secondary">
                                    {{ $teamMember->roleInTeam->label() }}
                                    @if ($teamMember->roleInTeam === RoleInTeam::PLAYER && $teamMember->roleInGame)
                                    <span aria-hidden="true">•</span>
                                    {{ $teamMember->roleInGame->label() }}
                                    @endif
                                </p>
                            </div>
                        </div>
                        <button
                            type="button"
                            data-team-link
                            wire:click="selectTeam({{ $teamMember->team_id }})"
                            title="{{ __('pages/team/index.access_team_title') }}"
                            class="cta-primary w-full whitespace-nowrap md:w-auto">
                            {{ __('pages/team/index.access_team_cta') }}
                        </button>
                    </div>
                </article>
            </li>
            @endforeach
            @endif
        </x-accordion>

        {{-- Invitations reçues --}}
        <x-accordion
            :headingLevel="'h3'"
            :title="__('pages/team/index.received_invitations_title')"
            :open="true"
            :count="$pendingInvitations->count()"
            :panelClass="'mt-6 flex flex-col gap-4'"
            class="lg:col-span-5">
            @if ($pendingInvitations->isEmpty())
            <li class="bg-bg-card p-6  text-center text-text-secondary">
                {{ __('pages/team/index.received_invitations_empty') }}
            </li>
            @else
            @foreach ($pendingInvitations as $invitation)
            <li wire:key="invitation-{{ $invitation->id }}">
                <article x-data x-on:click="$el.querySelector('[data-pending-invitation-link]')?.click()" class="cursor-pointer card-animated-border relative flex flex-col gap-4 border-l-2 border-gold bg-bg-card p-4 shadow-basic md:p-5">
                    <span class="card-animated-border-right-edge" aria-hidden="true"></span>
                    <div class="relative z-[1] flex flex-row flex-wrap items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="size-14 shrink-0 overflow-hidden">
                                <x-team-logo
                                    :team="$invitation->team"
                                    preset="pending-row"
                                    class="size-full object-cover" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <h3 class="truncate text-xl font-bold text-white">{{ $invitation->team->name }}</h3>
                                <p class="truncate text-sm text-text-secondary">
                                    {{ $invitation->roleInTeam->label() }}
                                    @if ($invitation->roleInTeam === RoleInTeam::PLAYER && $invitation->roleInGame)
                                    <span aria-hidden="true">•</span>
                                    {{ $invitation->roleInGame->label() }}
                                    @endif
                                </p>
                            </div>
                        </div>
                        <button
                            type="button"
                            data-pending-invitation-link
                            wire:click="openTeamInvitationModal({{ $invitation->id }})"
                            title="{{ __('pages/team/index.view_invitation_title') }}"
                            class="cta-primary w-full sm:w-auto">
                            {{ __('pages/team/index.view_invitation_cta') }}
                        </button>
                    </div>
                </article>
            </li>
            @endforeach
            @endif
        </x-accordion>
    </section>

    @if ($sentApplications->isNotEmpty())
    {{-- Candidatures envoyées --}}
    <section class="flex flex-col gap-6">
        <h2 class="text-2xl font-bold">
            {{ __('pages/team/index.sent_applications_title') }}
            <span class="text-gold font-bold">({{ $sentApplications->count() }})</span>
        </h2>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[680px] shadow-basic">
                <thead class="bg-input-bg">
                    <tr>
                        <th class="p-6 text-left">{{ __('pages/team/index.sent_applications_column_team') }}</th>
                        <th class="p-6 text-left">{{ __('pages/team/index.sent_applications_column_role') }}</th>
                        <th class="p-6 text-left">{{ __('pages/team/index.sent_applications_column_date') }}</th>
                        <th class="p-6 text-left">{{ __('pages/team/index.sent_applications_column_status') }}</th>
                        <th class="p-6 text-right">{{ __('pages/team/index.sent_applications_column_action') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-bg-widget">
                    @foreach ($sentApplications as $application)
                    <tr wire:key="application-{{ $application->id }}">
                        <td class="p-6">
                            <div class="flex items-center gap-3">
                                <div class="size-9 shrink-0 overflow-hidden">
                                    <x-team-logo
                                        :team="$application->team"
                                        preset="thumbnail"
                                        class="size-full object-cover" />
                                </div>
                                <span class="font-bold">{{ $application->team->name }}</span>
                            </div>
                        </td>
                        <td class="p-6">
                            @if ($application->roleInTeam === RoleInTeam::COACH || $application->roleInTeam === RoleInTeam::STAFF)
                            {{ $application->roleInTeam->label() }}
                            @elseif ($application->roleInGame)
                            {{ $application->roleInGame->label() }}
                            @else
                            {{ $application->roleInTeam->label() }}
                            @endif
                        </td>
                        <td class="p-6 text-text-secondary">
                            {{ $application->created_at->isoFormat('DD MMMM YYYY') }}
                        </td>
                        <td class="p-6">
                            <span class="inline-flex items-center gap-2 font-semibold text-white {{ $application->status->macaron() }}">
                                {{ $application->status->label() }}
                            </span>
                        </td>
                        <td class="p-6">
                            <div class="flex justify-end">
                                @switch($application->status)
                                @case(StatusApplication::PENDING)
                                <button
                                    wire:click="openModalCancelApplication({{ $application->id }})"
                                    type="button"
                                    title="{{ __('pages/team/index.sent_applications_cancel_title') }}"
                                    class="cta-danger cta-danger--outline inline-flex size-9 shrink-0 items-center justify-center !p-0">
                                    <flux:icon name="x-mark" class="size-5" />
                                    <span class="sr-only">{{ __('pages/team/index.sent_applications_cancel_title') }}</span>
                                </button>
                                @break
                                @case(StatusApplication::ACCEPTED)
                                <span
                                    class="flex size-9 items-center justify-center text-task-done"
                                    title="{{ __('pages/team/index.sent_applications_accepted_title') }}"
                                    aria-hidden="true">
                                    <flux:icon name="check-circle" class="size-6" />
                                </span>
                                @break
                                @case(StatusApplication::REJECTED)
                                <span
                                    class="flex size-9 items-center justify-center text-text-secondary"
                                    title="{{ __('pages/team/index.sent_applications_rejected_title') }}"
                                    aria-hidden="true">
                                    <flux:icon name="information-circle" class="size-6" />
                                </span>
                                @break
                                @endswitch
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
    @endif
</div>