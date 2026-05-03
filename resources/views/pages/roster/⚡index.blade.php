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
use Illuminate\Support\Facades\Auth;
use App\Enums\StatusInTeam;

new #[Layout('layouts::team')] class extends Component {


    #[Computed]
    public function candidates(): \Illuminate\Database\Eloquent\Collection
    {
        return TeamApplication::where('team_id', currentTeam()->id)
            ->where('status', StatusApplication::PENDING)
            ->with('user')
            ->get();
    }

    #[Computed]
    public function isStarter(): \Illuminate\Database\Eloquent\Collection
    {
        return TeamMember::where('status', StatusInTeam::ACCEPTED)
            ->where('team_id', currentTeam()->id)
            ->where('is_starter', true)
            ->with('user')
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

    public function openTeamApplicationModal($candidateId): void
    {
        $this->dispatch('open_modal', [
            'form' => 'team-application',
            'model_id' => $candidateId,
        ]);
    }

    public function promoteToStarter(int $teamMemberId): void
    {
        //
    }

    public function openModalKickTeamMember(int $teamMemberId): void
    {
        $this->dispatch('open_modal', [
            'form' => 'kick-team-member',
            'model_id' => $teamMemberId,
        ]);
    }
};
?>

<div class="flex flex-col gap-6">
    <section x-data="{ openCandidates: true }" class="p-6 bg-bg-widget basic-shadow flex flex-col">
        <div class="flex items-center gap-4 justify-between">
            <h2 class="text-[32px] font-bold">
                {{ __('pages/roster/index.application_title') }} <span class="text-gold font-bold">({{ $this->candidates->count() }})</span>
            </h2>

            <button class="group cursor-pointer" x-on:click.prevent="openCandidates = !openCandidates">
                <flux:icon.chevron-down
                    class="size-8 transition-all duration-150 ease-in-out text-text-gray group-hover:text-gold"
                    ::class="openCandidates ? 'rotate-0 text-gold' : '-rotate-90 text-text-gray'" />
            </button>
        </div>
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
                            @if ($candidate->user->avatar)
                            <img class="aspect-square h-auto w-16 shrink-0 object-cover md:w-20 xl:w-[96px]" src="{{Storage::disk('public')->url('images/avatar/variants/480x480/' . $candidate->user->avatar)}}" alt="Photo de profil de {{ $candidate->user->username }}">
                            @else
                            <img src="{{ asset('/img/basicIcon.webp') }}" class="aspect-square h-auto w-16 shrink-0 object-cover md:w-20 xl:w-[96px]" alt="Photo de profil de {{ $candidate->user->username }}">
                            @endif
                            <div class="min-w-0">
                                <h3 class="truncate text-lg font-bold text-gold md:text-xl xl:text-2xl">{{ $candidate->user->username }}</h3>
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
    </section>
    <section x-data="{ openIsStarter: true }" class="p-6 bg-bg-widget basic-shadow flex flex-col">
        <div class="flex items-center gap-4 justify-between">
            <h2 class="text-[32px] font-bold">
                {{ __('pages/roster/index.is_starter_title') }} <span class="text-gold font-bold">({{ $this->candidates->count() }})</span>
            </h2>
            <button class="group cursor-pointer" x-on:click.prevent="openIsStarter = !openIsStarter">
                <flux:icon.chevron-down
                    class="size-8 transition-all duration-150 ease-in-out text-text-gray group-hover:text-gold"
                    ::class="openIsStarter ? 'rotate-0 text-gold' : '-rotate-90 text-text-gray'" />
            </button>
        </div>
        <ul class="mt-6 grid grid-cols-12 gap-4 md:gap-6"
            x-show="openIsStarter"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-2">
            @foreach ($this->isStarter as $teamMember)
            <li class="col-span-12 md:col-span-4">
                <x-cards.player :team-member="$teamMember" />
            </li>
            @endforeach
        </ul>

    </section>
</div>