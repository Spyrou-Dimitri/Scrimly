<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use App\Models\TeamApplication;
use App\Enums\RoleInTeam;
use App\Enums\StatusApplication;
use Illuminate\Database\Eloquent\Collection;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Enums\StatusInTeam;

new #[Layout('layouts::team')] class extends Component {


    #[Computed]
    public function candidates(): \Illuminate\Database\Eloquent\Collection
    {
        return TeamApplication::where('team_id', currentTeam()->id)
            ->where('status', StatusApplication::PENDING)
            ->with(['user.riotProfile'])
            ->get();
    }

    #[Computed]
    public function isStarter(): \Illuminate\Database\Eloquent\Collection
    {
        return TeamMember::where('status', StatusInTeam::ACCEPTED)
            ->where('team_id', currentTeam()->id)
            ->where('status', StatusInTeam::ACCEPTED)
            ->with(['user.riotProfile'])
            ->get();
    }

    #[On('refresh_candidates')]
    public function refreshCandidates(): void
    {
        unset($this->candidates);
    }
    #[On('refresh_roster')]
    public function refreshRoster(): void
    {
        unset($this->isStarter);
    }

    public function openTeamApplicationModal(int $candidateId): void
    {
        $candidate = TeamApplication::query()
            ->where('team_id', currentTeam()->id)
            ->findOrFail($candidateId);

        if (Gate::denies('view', $candidate)) {
            $this->dispatch('toast', [
                'title' => __('policies/roster.error_title'),
                'message' => __('policies/roster.error_view_application'),
                'type' => 'error',
            ]);

            return;
        }

        $this->dispatch('open_modal', [
            'form' => 'team-application',
            'model_id' => $candidateId,
        ]);
    }

    public function openModalPromoteToStarter(int $teamMemberId): void
    {
        if (Gate::denies('manageTeam', User::class)) {
            $this->dispatch('toast', [
                'title' => __('policies/roster.error_title'),
                'message' => __('policies/roster.error_manage_roster'),
                'type' => 'error',
            ]);

            return;
        }

        $this->dispatch('open_modal', [
            'form' => 'promote-to-starter',
            'model_id' => $teamMemberId,
        ]);
    }
    public function openModalSendToBench(int $teamMemberId): void
    {
        if (Gate::denies('manageTeam', User::class)) {
            $this->dispatch('toast', [
                'title' => __('policies/roster.error_title'),
                'message' => __('policies/roster.error_manage_roster'),
                'type' => 'error',
            ]);

            return;
        }

        $this->dispatch('open_modal', [
            'form' => 'send-to-bench',
            'model_id' => $teamMemberId,
        ]);
    }

    public function openModalKickTeamMember(int $teamMemberId): void
    {
        if (Gate::denies('manageTeam', User::class)) {
            $this->dispatch('toast', [
                'title' => __('policies/roster.error_title'),
                'message' => __('policies/roster.error_manage_roster'),
                'type' => 'error',
            ]);

            return;
        }

        $this->dispatch('open_modal', [
            'form' => 'kick-team-member',
            'model_id' => $teamMemberId,
        ]);
    }
};
?>

<div class="flex flex-col gap-6">
    <section class="flex flex-col gap-6">
        <div class="flex items-center gap-4 justify-between">
            <h2 class="text-[32px] font-bold">
                {{ __('pages/roster/index.title') }}
            </h2>
            <x-cta :href="route('team.invitations.create', currentTeam()->slug)" :title="__('pages/roster/index.create_invitation_title')" :class="'primary'">
                {{ __('pages/roster/index.create_invitation_cta') }}
            </x-cta>
        </div>
        {{-- Candidatures --}}
        <section x-data="{ openCandidates: true }" class="p-6 bg-bg-widget basic-shadow flex flex-col">
            <div class="flex items-center gap-4 justify-between">
                <h3 class="text-2xl text-gold font-bold">
                    {{ __('pages/roster/index.application_title') }} <span class="text-gold font-bold">({{ $this->candidates->where('status', StatusApplication::PENDING)->count() }})</span>
                </h3>

                <button class="group cursor-pointer" x-on:click.prevent="openCandidates = !openCandidates">
                    <flux:icon.chevron-down
                        class="size-8 transition-all duration-150 ease-in-out text-text-gray group-hover:text-gold"
                        ::class="openCandidates ? 'rotate-0 text-gold' : '-rotate-90 text-text-gray'" />
                </button>
            </div>
            @if ($this->candidates->count() > 0)
            <ul class="flex flex-col gap-4 mt-6"
                x-show="openCandidates"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 -translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 -translate-y-2">
                @foreach ($this->candidates as $candidate)
                <li wire:click="openTeamApplicationModal({{ $candidate->id }})" class="card-animated-border bg-bg-card relative cursor-pointer border-l-2 border-gold p-4 basic-shadow md:p-5 xl:p-6">
                    <span class="card-animated-border-right-edge" aria-hidden="true"></span>
                    <div class="relative z-[1] flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between xl:gap-4">
                        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between md:gap-4 xl:contents">
                            <div class="flex min-w-0 shrink-0 items-center gap-3 md:gap-4">
                                <x-user-avatar
                                    :user="$candidate->user"
                                    preset="roster-candidate"
                                    class="aspect-square h-auto w-16 shrink-0 object-cover md:w-20 xl:w-[96px]" />
                                <div class="min-w-0">
                                    <h4 class="truncate text-lg font-bold text-gold text-xl">{{ $candidate->user->username }}</h4>
                                    <p class="truncate text-sm text-text-gray md:text-base">{{ $candidate->user->riot_tag }}</p>
                                </div>
                            </div>

                            <div class="grid min-w-0 flex-1 grid-cols-2 gap-3  md:gap-4 md:p-4 xl:flex xl:max-w-none xl:flex-initial xl:items-stretch xl:gap-0 xl:border-0 xl:bg-transparent xl:p-0">
                                <div class="flex min-w-0 flex-col justify-center xl:pr-8">
                                    <p class="text-center text-xs text-text-gray md:text-sm xl:text-center">Role souhaité</p>
                                    <div class="mt-1 flex items-center justify-center gap-2 text-base font-bold text-gold md:text-lg xl:text-xl">
                                        @if ($candidate->roleInTeam === RoleInTeam::COACH || $candidate->roleInTeam === RoleInTeam::STAFF)
                                        <p class="min-w-0 truncate text-center">{{ $candidate->roleInTeam->label() }}</p>
                                        @else
                                        <img src="{{ asset($candidate->roleInGame->icon()) }}" class="size-5 shrink-0 md:size-6" alt="{{ $candidate->roleInGame->label() }}">
                                        <p class="min-w-0 truncate">{{ $candidate->roleInGame->label() }}</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex min-w-0 flex-col justify-center border-l border-border-gold pl-3 md:pl-4 xl:border-l xl:pl-8">
                                    <p class="text-center text-xs text-text-gray md:text-sm xl:text-center">Rang actuel</p>
                                    <div class="mt-1 flex items-center justify-center gap-2 text-base font-bold text-white md:text-lg xl:text-xl">
                                        @if ($candidate->user->tier)
                                        <img src="{{ asset($candidate->user->tier->icon()) }}" class="size-7 shrink-0 md:size-8" alt="{{ $candidate->user->tier->label() }}">
                                        <p class="min-w-0 truncate text-center">{{ $candidate->user->tier->label() }} {{ $candidate->user->rank }}</p>
                                        @else
                                        <p class="inline-block">-</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="cta-primary w-full shrink-0 xl:w-auto" wire:click.prevent="openTeamApplicationModal({{ $candidate->id }})">
                            Voir la candidature
                        </button>
                    </div>

                </li>
                @endforeach
            </ul>
            @endif
        </section>
        {{-- Roster Principal --}}
        <x-accordion :headingLevel="'h3'" :title="__('pages/roster/index.is_starter_title')" :open="true" :count="$this->isStarter->where('is_starter', true)->where('roleInTeam', RoleInTeam::PLAYER)->count()">
            @foreach ($this->isStarter->where('is_starter', true)->where('roleInTeam', RoleInTeam::PLAYER) as $teamMember)
            <li class="col-span-12 md:col-span-4">
                <x-cards.player :team-member="$teamMember" />
            </li>
            @endforeach
        </x-accordion>
        {{-- Remplacants--}}
        <x-accordion :headingLevel="'h3'" :title="__('pages/team/index.bench_title')" :open="false" :count="$this->isStarter->where('is_starter', false)->where('roleInTeam', RoleInTeam::PLAYER)->count()">
            @foreach ($this->isStarter->where('is_starter', false)->where('roleInTeam', RoleInTeam::PLAYER) as $teamMember)
            <li class="col-span-12 md:col-span-4">
                <x-cards.player :team-member="$teamMember" />
            </li>
            @endforeach
        </x-accordion>
        {{-- Staff --}}
        <x-accordion :headingLevel="'h3'" :title="__('pages/team/index.staff_title')" :open="false" :count="$this->isStarter->whereIn('roleInTeam', [RoleInTeam::STAFF, RoleInTeam::COACH])->count()">
            @foreach ($this->isStarter->whereIn('roleInTeam', [RoleInTeam::STAFF, RoleInTeam::COACH]) as $teamMember)
            <li class="col-span-12 md:col-span-4">
                <x-cards.player :team-member="$teamMember" />
            </li>
            @endforeach
        </x-accordion>
    </section>

</div>