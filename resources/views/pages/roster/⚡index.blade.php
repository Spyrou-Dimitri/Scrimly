<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Collection;
use App\Models\Team;
use App\Models\TeamApplication;
use App\Enums\RoleInTeam;

new #[Layout('layouts::team')] class extends Component {
    public Collection $candidates;
    public function mount()
    {
        $this->candidates = TeamApplication::where('team_id', currentTeam()->id)->with('user')->get();
    }

    public function openTeamApplicationModal($candidateId): void
    {
        $this->dispatch('open_modal', [
            'form' => 'team-application',
            'model_id' => $candidateId,
        ]);
    }


};
?>

<div>
    <section class="p-6 bg-bg-widget basic-shadow flex flex-col gap-6">
        <div class="flex items-center gap-4 justify-between">
            <div class="flex items-center gap-2">
                <h2 class="text-[32px] font-bold">
                    {{ __('pages/roster/index.application_title') }}
                </h2>
                <flux:icon.chevron-right class="size-8" />
            </div>
            <p class="text-text-gray">
                {{ $this->candidates->count() }} candidatures en attente de révisions
            </p>
        </div>
        <ul class="flex flex-col gap-4">
            @foreach ($this->candidates as $candidate)
            <li wire:click="openTeamApplicationModal({{ $candidate->id }})" class="card-animated-border bg-bg-card cursor-pointer basic-shadow border-l-2 border-gold relative p-6">
                <span class="card-animated-border-right-edge" aria-hidden="true"></span>
                <div class="relative z-[1] flex justify-between items-center gap-4">
                    <div class="flex items-center gap-4">
                        <img class="w-[96px] h-auto object-cover" src="{{Storage::disk('public')->url('images/avatar/variants/480x480/' . $candidate->user->avatar)}}" alt="Photo de profil de {{ $candidate->user->username }}">
                        <div>
                            <h3 class="text-2xl text-gold font-bold">{{ $candidate->user->username }}</h3>
                            <p class="text-text-gray">{{ $candidate->user->riot_tag }}</p>
                        </div>
                    </div>

                    <div class="flex">
                        <div class="pr-8">
                            <p class="text-center text-text-gray">
                                Role souhaité</p>
                            <div class="block text-xl text-gold font-bold flex justify-center items-center gap-2">
                                @if ($candidate->roleInTeam === RoleInTeam::COACH || $candidate->roleInTeam === RoleInTeam::STAFF)
                                <p class="inline-block">{{ $candidate->roleInTeam->label() }}</p>
                                @else
                                <img src="{{ asset($candidate->roleInGame->icon()) }}" class="w-6 h-6" alt="{{ $candidate->roleInGame->label() }}">

                                <p class="inline-block">{{ $candidate->roleInGame->label() }}</p>
                                @endif
                            </div>
                        </div>
                        <div class="border-l border-border-gold pl-8">
                            <p class="text-center text-text-gray">Rang actuel</p>
                            <div class="block text-xl font-bold text-white flex justify-center items-center gap-2">
                                <img src="{{ asset($candidate->user->tier->icon()) }}" class="w-8 h-8" alt="{{ $candidate->user->tier->label() }}">
                                <p class="inline-block">{{ $candidate->user->tier->label() }} {{ $candidate->user->rank }}</p>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="cta-primary" wire:click.prevent="openTeamApplicationModal({{ $candidate->id }})">
                        Voir la candidature
                    </button>
                </div>

            </li>
            @endforeach
        </ul>
    </section>
</div>