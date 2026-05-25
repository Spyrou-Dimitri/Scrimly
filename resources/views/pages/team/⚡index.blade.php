<?php

use App\Enums\RoleInTeam;
use App\Enums\StatusApplication;
use App\Enums\StatusInTeam;
use App\Models\TeamApplication;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::choose_a_team')] class extends Component
{
    public User $currentUser;

    public Collection $teamMembers;

    public Collection $pendingApplications;

    public function mount(): void
    {
        $this->currentUser = Auth::user();

        if ($this->currentUser->current_team_id !== null) {
            $this->currentUser->update(['current_team_id' => null]);
            $this->currentUser->refresh();
        }

        $this->teamMembers = TeamMember::where('user_id', $this->currentUser->id)
            ->where('status', StatusInTeam::ACCEPTED)
            ->with('team')
            ->get();

        $this->pendingApplications = TeamApplication::query()
            ->where('user_id', $this->currentUser->id)
            ->where('status', StatusApplication::PENDING)
            ->with('team')
            ->latest()
            ->get();
    }

    public function selectTeam($teamId)
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
    
    
};
?>

<div class="font-spaceGrotesk w-full">
    <section class="flex w-full flex-col items-center gap-6 justify-center">
        <div>
            @if($currentUser->teams->count() === 0)
            <h2 class="text-[40px] font-bold text-center">
                {{ __('pages/team/index.onboarding_title') }} <span class="text-gold font-bold">{{$currentUser->username}}</span>
            </h2>
            <p class="text-center text-2xl">
                {{ __('pages/team/index.onboarding_description') }}
            </p>
            @else
            <h2 class="text-[40px] font-bold text-center">
                {{ __('pages/team/index.title') }} <span class="text-gold font-bold">{{$currentUser->username}}</span> !
            </h2>
            <p class="text-center text-2xl">
                {{ __('pages/team/index.description') }}
            </p>
            @endif
        </div>
        <ul class="flex w-full flex-row gap-6 justify-center flex-wrap">
            @foreach ($teamMembers as $teamMember)
            <li class="w-full md:w-[calc(33.33%-1.125rem)] xl:w-[calc(20%-1.125rem)]">
                <article class="card-animated-border relative flex min-h-full w-full cursor-pointer flex-col items-center justify-between gap-4 border-l-2 border-gold bg-[#333237] p-6">
                    <span class="card-animated-border-right-edge" aria-hidden="true"></span>
                    <button
                        type="button"
                        wire:click="selectTeam({{ $teamMember->team_id }})"
                        class="absolute inset-0 z-10 cursor-pointer rounded-[inherit] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gold"
                        aria-label="{{ $teamMember->team->name }}"></button>

                    <div class="relative z-[1] flex min-h-full w-full flex-col items-center justify-between gap-4 pointer-events-none">
                        <img src="{{ $teamMember->team->logo_url }}" class="m-auto h-auto w-[250px] object-fit" alt="{{ $teamMember->team->name }}">
                        <h3 class="text-center text-2xl font-bold">{{ $teamMember->team->name }}</h3>
                        <p class="text-center  text-text-secondary">
                            {{ $teamMember->roleInTeam->label() }}
                            @if ($teamMember->roleInTeam === RoleInTeam::PLAYER)
                                {{ $teamMember->roleInGame->label() }}
                            @endif
                        </p>
                    </div>
                </article>
            </li>
            @endforeach
        </ul>
        <div class="flex flex-row gap-6">
            <x-cta :href="route('team.join')" :title="__('pages/team/index.join_team_title')" :class="'secondary'">
                {{ __('pages/team/index.join_team_cta') }}
            </x-cta>
            <x-cta :href="route('team.create')" :title="__('pages/team/index.create_team_title')" :class="'primary'">
                {{ __('pages/team/index.create_team_cta') }}
            </x-cta>

        </div>

        @if ($pendingApplications->isNotEmpty())
            <aside
                class="mt-4 w-full max-w-4xl shadow-basic bg-bg-widget p-6"
                aria-labelledby="pending-applications-heading">
                <h3 id="pending-applications-heading" class="mb-4 text-center text-2xl font-bold">
                    {{ __('pages/team/index.pending_applications_title') }}
                    <span class="text-gold">({{ $pendingApplications->count() }})</span>
                </h3>

                <ul class="flex flex-col gap-3" role="list">
                    @foreach ($pendingApplications as $application)
                        <li
                            wire:key="pending-application-{{ $application->id }}"
                            class="flex flex-wrap items-center gap-4 shadow-basic bg-bg-card p-4">
                            <img
                                src="{{ $application->team->logo_url }}"
                                alt="{{ $application->team->name }}"
                                class="size-12 shrink-0 object-contain sm:size-14">

                            <div class="min-w-0 flex-1">
                                <h4 class="truncate text-xl font-bold">{{ $application->team->name }}</h4>
                                <p class="text text-text-secondary">
                                    @if ($application->roleInTeam === RoleInTeam::COACH || $application->roleInTeam === RoleInTeam::STAFF)
                                        {{ $application->roleInTeam->label() }}
                                    @else
                                        {{ $application->roleInGame->label() }}
                                    @endif
                                </p>
                            </div>

                            <p class="shrink-0 text-sm font-semibold text-gold">
                                {{ $application->status->label() }}
                            </p>
                        </li>
                    @endforeach
                </ul>
            </aside>
        @endif

    </section>
</div>