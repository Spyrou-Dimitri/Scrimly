<?php

use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Collection;
use App\Models\TeamMember;
use App\Enums\StatusInTeam;
new #[Layout('layouts::choose_a_team')] class extends Component
{
    public User $currentUser;
    public Collection $teamMembers;

    public function mount()
    {
        $this->currentUser = Auth::user();
        $this->teamMembers = TeamMember::where('user_id', $this->currentUser->id)
            ->where('status', StatusInTeam::ACCEPTED)
            ->with('team')
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
            <li class="w-full md:w-[calc(33.33%-1.125rem)] xl:w-[calc(20%-1.125rem)] min-h-[350px] border border-[rgba(255,255,255,0.3)] hover:border-gold transition-all  ease-in-out duration-150 hover:translate-y-[-10px]">
                <article class="flex w-full flex-col items-center justify-between min-h-full gap-4 p-6 bg-[#333237] relative ">
                    <button type="button" wire:click="selectTeam({{ $teamMember->team_id }})" class="absolute inset-0 cursor-pointer" aria-label="{{ $teamMember->team->name }}"></button>
                    <img src="{{ $teamMember->team->logo_url }}" class="w-[250px] h-auto m-auto object-fit" alt="{{ $teamMember->team->name }}">
                    
                    <h3 class="text-2xl font-bold text-center">{{ $teamMember->team->name }}</h3>
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

    </section>
</div>