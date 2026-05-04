<?php

use Livewire\Component;
use App\Models\TeamApplication;
use App\Enums\RoleInTeam;
use App\Enums\RoleInGame;
use App\Enums\StatusApplication;
use App\Models\TeamMember;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use App\Enums\StatusInTeam;

new class extends Component
{
    public TeamApplication $candidate;
    public ?RoleInTeam $roleInTeam = null;
    public ?RoleInGame $roleInGame = null;
    public ?string $is_starter = null;

    public function mount($model_id)
    {
        $this->candidate = TeamApplication::query()
            ->with(['user.riotProfile'])
            ->findOrFail($model_id);
        $this->roleInTeam = $this->candidate->roleInTeam;
        $this->roleInGame = $this->candidate->roleInGame;
    }
    public function refuse()
    {
        $this->candidate->update([
            'status' => StatusApplication::REJECTED,
        ]);
        $this->dispatch('refresh_candidates');
        $this->dispatch('close_modal');
        $this->dispatch('toast', [
            'type' => 'error',
            'message' => 'Candidature refusée',
        ]);
    }
    public function accept()
    {
        $this->validate([
            'is_starter' => [
                Rule::requiredIf(fn() => $this->roleInTeam === RoleInTeam::PLAYER),
                'nullable',
                'boolean',
            ],
        ], [
            'is_starter.required' => 'Veuillez choisir un statut (Titulaire ou Remplacant).',
        ]);


        DB::transaction(function () {
            $this->candidate->update([
                'status' => StatusApplication::ACCEPTED,
            ]);
            TeamMember::updateOrCreate(
                [
                    'user_id' => $this->candidate->user_id,
                    'team_id' => $this->candidate->team_id,
                ],
                [
                    'roleInTeam' => $this->roleInTeam,
                    'roleInGame' => $this->roleInGame,
                    'is_starter' => (bool) $this->is_starter,
                    'status' => StatusInTeam::ACCEPTED,
                    'joined_at' => now(),
                ]
            );
        });
        $this->dispatch('refresh_candidates');
        $this->dispatch('close_modal');
        $this->dispatch('toast', [
            'type' => 'success',
            'message' => 'Candidature acceptée',
        ]);
    }
    #[Computed]
    public function existingStarterRoleInGame()
    {
        if ($this->is_starter !== '1' || $this->candidate->roleInTeam !== RoleInTeam::PLAYER) {
            return null;
        }
        return TeamMember::where('team_id', $this->candidate->team_id)
            ->where('roleInGame', $this->candidate->roleInGame)
            ->where('is_starter', true)
            ->first();
    }
};
?>

<div>
    <x-layout.head-modal :width="'5xl'" :height="'75'" :title="__('modals/team-application.title') . ' ' . $this->candidate->user->username">
        <div class="flex flex-col md:flex-row md:items-center lg:grid lg:grid-cols-12 gap-6">
            <div class="flex flex-col md:flex-row items-center gap-4 md:items-center md:shrink-0 lg:col-span-3">
                @if ($this->candidate->user->avatar)
                <img class="w-full h-auto md:w-20 md:h-20 object-cover" src="{{Storage::disk('public')->url('images/avatar/variants/480x480/' . $this->candidate->user->avatar)}}" alt="Photo de profil de {{ $this->candidate->user->username }}">
                @else
                <img src="{{ asset('/img/basicIcon.webp') }}" class="w-full h-auto md:w-20 md:h-20 object-cover" alt="Photo de profil de {{ $this->candidate->user->username }}">
                @endif
                <div class="text-center md:text-left">
                    <h3 class="text-2xl text-gold font-bold">{{ $this->candidate->user->username }}</h3>
                    <p class="text-text-gray">{{ $this->candidate->user->riot_tag }}</p>
                </div>
            </div>
            <div class="flex flex-wrap justify-around gap-6 md:flex-1 lg:contents">
                <div class="text-center lg:col-span-3">
                    <p class="text-text-gray">Role souhaite</p>
                    <div class="text-xl font-bold text-white flex justify-center items-center gap-2">
                        @if ($candidate->roleInTeam === RoleInTeam::COACH || $candidate->roleInTeam === RoleInTeam::STAFF)
                        <p>{{ $candidate->roleInTeam->label() }}</p>
                        @else
                        <img src="{{ asset($this->candidate->roleInGame->icon()) }}" class="w-8 h-8" alt="{{ $this->candidate->roleInGame->label() }}">
                        <p>{{ $this->candidate->roleInGame->label() }}</p>
                        @endif
                    </div>
                </div>
                <div class="text-center lg:col-span-3">
                    <p class="text-text-gray">Rang actuel</p>
                    <div class="text-xl font-bold text-white flex justify-center items-center gap-2">
                        @if ($this->candidate->user->tier)
                        <img src="{{ asset($this->candidate->user->tier->icon()) }}" class="w-8 h-8" alt="{{ $this->candidate->user->tier->label() }}">
                        @endif
                        @if ($this->candidate->user->tier)
                        <p>{{ $this->candidate->user->tier->label() }} {{ $this->candidate->user->rank }}</p>
                        @else
                        <p>-</p>
                        @endif
                    </div>
                </div>
                <div class="text-center lg:col-span-3">
                    <p class="text-text-gray">Winrate</p>
                    <p class="text-gold text-xl font-bold">66%</p>
                    <p class="text-text-white text-sm">67V / 33D</p>
                </div>
            </div>
        </div>
        <div class="flex flex-col gap-2">
            <h3 class="text-gold font-bold text-2xl">Motivation</h3>
            <p class="text-white">{{ $this->candidate->motivation }}</p>
        </div>
        @if ($this->candidate->roleInTeam === RoleInTeam::PLAYER)
        <div class="border-t-2 border-gray-500 pt-6">
            <fieldset>
                <legend class="mb-6 text-gold font-bold text-xl lg:text-2xl">
                    Gestion de la candidature
                </legend>
                <div class="grid gap-4">
                    <div>
                        <x-forms.radio
                            name="is_starter"
                            label="Statut"
                            wire:model.live="is_starter"
                            :columns="2"
                            :required="true"
                            :options="[
                            ['value' => '1', 'label' => 'Titulaire'],
                            ['value' => '0', 'label' => 'Remplaçant'],
                        ]" />
                        @error('is_starter')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                @if ($this->existingStarterRoleInGame)
                <div class="flex items-center bg-red-900/60 mt-2 text-white p-2 gap-2">
                    <flux:icon name="exclamation-triangle" variant="outline" class="w-12 h-12" />
                    <p>
                        <span class="font-bold">{{$this->existingStarterRoleInGame->user->username}}</span> est déjà titulaire <span class="font-bold">{{$this->existingStarterRoleInGame->roleInGame->label()}}</span>. Il sera automatiquement passé en remplaçant si vous acceptez <span class="font-bold">{{$this->candidate->user->username}}</span> comme titulaire.
                    </p>
                </div>
                @endif
                <div class="flex justify-between gap-2 mt-6">
                    <button wire:click="refuse" class="cta-secondary">
                        Refuser
                    </button>
                    <button wire:click="accept" class="cta-primary">
                        Accepter
                    </button>
                </div>
            </fieldset>
        </div>
        @else
        <div class="flex justify-between gap-2">
            <button wire:click="refuse" class="cta-secondary">
                Refuser
            </button>
            <button wire:click="accept" class="cta-primary">
                Accepter
            </button>
        </div>
        @endif
</div>
</x-layout.head-modal>
</div>