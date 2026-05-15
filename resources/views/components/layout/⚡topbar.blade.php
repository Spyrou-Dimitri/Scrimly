<?php

use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;

new class extends Component
{
    public ?Team $team = null;
    public Collection $userTeams;

    #[Computed]
    public function currentUser(): ?User
    {
        return Auth::user();
    }

    public function mount(): void
    {
        $this->team = currentTeam();
        $this->userTeams = collect();
    }

    public function loadTeams(): void
    {
        $this->userTeams = $this->currentUser
            ->teams()
            ->select('teams.id', 'teams.name', 'teams.slug', 'teams.logo_type', 'teams.logo_value')
            ->where('teams.id', '!=', $this->currentUser->current_team_id)
            ->get();
    }

    public function unloadTeams(): void
    {
        $this->userTeams = collect();
    }

    public function switchTeam(int $teamId): void
    {
        $user = $this->currentUser;

        $belongsToTeam = $user->teams()
            ->where('teams.id', $teamId)
            ->exists();

        if (! $belongsToTeam) {
            abort(403);
        }

        $user->update(['current_team_id' => $teamId]);

        $team = Team::select('slug')->findOrFail($teamId);

        $this->redirectRoute('roster.index', ['slug' => $team->slug]);
    }
};
?>

<header

    class="flex-shrink-0 h-16 flex items-center justify-between
           px-4 lg:px-6
           bg-bg-main border-b basic-shadow border-[#2C2D34]">
    <div class="flex items-center gap-3 min-w-0">
        <button
            type="button"
            x-data
            @click="$dispatch('sidebar-toggle')"
            class="lg:hidden p-2 -ml-2 text-white hover:text-gold transition-colors cursor-pointer"
            aria-label="{{ __('layouts/team.open_menu_aria') }}">
            <flux:icon.bars-3 class="size-6" />
        </button>

        @if ($team)
        <div x-data="{ openTeams: false }" @click.outside="if (openTeams) { openTeams = false; $wire.unloadTeams() }" class="relative">
            <h2>
                <button type="button"
                    @click="openTeams = !openTeams; if (openTeams) $wire.loadTeams(); if (!openTeams) $wire.unloadTeams()"
                    type="button" class="flex items-center text-white gap-3 min-w-0 hover:text-gold transition-colors cursor-pointer">
                    <img
                        src="{{ $team->logo_url }}"
                        alt="{{ $team->name }}"
                        class="w-9 h-9 object-contain">
                    
                    <div class="flex items-center relative gap-2 transition ease-in-out duration-150 hover:text-gold">
                        <span class="text-inherit font-semibold text-base lg:text-lg truncate">
                            {{ $team->name }}
                        </span>
                        <flux:icon.chevron-down class="size-4" />
                    </div>

                </button>
            </h2>
            <ul
                x-show="openTeams"
                x-transition
                x-cloak
                class="absolute top-full mt-3 left-12 flex flex-col gap-4 w-56 origin-top shadow-lg bg-bg-widget p-4 z-50">
                <li class="">
                    <a href="{{ route('team.create') }}" title="{{ __('layouts/team.create_team_cta_title') }}" class="px-3 hover:text-gold transition ease-in-out duration-150 flex items-center gap-2 cursor-pointer">
                        <flux:icon.plus class="size-4" />
                        Créer une équipe
                    </a>
                </li>
                @foreach ($userTeams as $userTeam)
                <li class="">

                    <button wire:click="switchTeam({{ $userTeam->id }})" class="px-3 hover:text-gold transition ease-in-out duration-150 flex items-center gap-2 cursor-pointer">
                        @if ($userTeam->logo)
                        <img src="{{ Storage::disk('public')->url('images/logoTeam/variants/128x128/' . $userTeam->logo) }}"
                            alt="{{ $userTeam->name }}"
                            class="size-6 object-cover">
                        @else
                        <img src="{{ asset('/img/basicIcon.webp') }}"
                            alt="{{ $userTeam->name }}"
                            class="size-6 object-cover">
                        @endif
                        <span class="relative
                 before:content-[''] before:w-full before:h-[2px]
                 before:scale-x-0 before:bg-gold
                 before:absolute before:-bottom-0.5 before:left-0
                 before:origin-left
                 before:transition-transform before:duration-150 before:ease-in-out
                 group-hover:before:scale-x-100">
                            {{ $userTeam->name }}
                        </span>
                    </button>
                </li>
                @endforeach
            </ul>
            @endif
        </div>
    </div>

    <div class="flex items-center gap-3 lg:gap-5">
        <button

            class="relative p-2 text-white hover:text-gold transition-colors cursor-pointer"
            aria-label="{{ __('layouts/team.notifications_aria') }}">
            <flux:icon.bell class="size-6" />

            <span class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1
                     flex items-center justify-center
                     text-[11px] font-bold text-white
                     bg-red-600 rounded-full">
            </span>
        </button>

        @if ($this->currentUser)
        <div class="flex items-center gap-2 lg:gap-3">
            <a href="{{ route('profile.show') }}"
                title="{{ __('layouts/team.edit_profile_cta_title') }}"
                class="flex items-center gap-2 lg:gap-3 group">

                <img src="{{ $this->currentUser->avatar_url }}"
                    alt="{{ $this->currentUser->username }}"
                    class="size-9 rounded-full object-cover flex-shrink-0">
                

                <span class="hidden sm:inline-block relative text-white font-medium max-w-[160px]
                 before:content-[''] before:absolute before:bottom-0 before:left-0 
                 group-hover:text-gold
                 before:w-full before:h-[2px] before:bg-gold
                 before:scale-x-0 before:origin-left
                 before:transition-transform before:duration-150 before:ease-in-out 
                 group-hover:before:scale-x-100">
                    {{ $this->currentUser->username }}
                </span>
            </a>
        </div>
        @endif
    </div>
</header>