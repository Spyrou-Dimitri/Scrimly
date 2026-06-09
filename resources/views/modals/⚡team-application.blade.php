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
use Illuminate\Support\Facades\Gate;
use App\Models\User;

new class extends Component
{
    public TeamApplication $candidate;
    public ?RoleInTeam $roleInTeam = null;
    public ?RoleInGame $roleInGame = null;
    public ?string $is_starter = null;

    public function mount($model_id): void
    {
        $this->candidate = TeamApplication::query()
            ->with(['user.riotProfile'])
            ->where('team_id', currentTeam()->id)
            ->findOrFail($model_id);

        abort_unless(Gate::allows('view', $this->candidate), 403);

        $this->roleInTeam = $this->candidate->roleInTeam;
        $this->roleInGame = $this->candidate->roleInGame;
    }

    public function refuse(): void
    {
        if (Gate::denies('manageTeam', User::class)) {
            $this->dispatch('toast', [
                'title' => __('policies/roster.error_title'),
                'message' => __('policies/roster.error_manage_application'),
                'type' => 'error',
            ]);

            return;
        }

        $this->candidate->update([
            'status' => StatusApplication::REJECTED,
        ]);
        $this->dispatch('refresh_candidates');
        $this->dispatch('close_modal');
        $this->dispatch('toast', [
            'type' => 'error',
            'message' => __('toasts/toasts.application_refused'),
        ]);
    }

    public function accept(): void
    {
        if (Gate::denies('manageTeam', User::class)) {
            $this->dispatch('toast', [
                'title' => __('policies/roster.error_title'),
                'message' => __('policies/roster.error_manage_application'),
                'type' => 'error',
            ]);

            return;
        }

        $this->validate([
            'is_starter' => [
                Rule::requiredIf(fn() => $this->roleInTeam === RoleInTeam::PLAYER),
                'nullable',
                'boolean',
            ],
        ], [
            'is_starter.required' => __('modals/team-application.is_starter_required'),
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
            $this->candidate->team->averageEloScore();
        });
        $this->dispatch('refresh_candidates');
        $this->dispatch('close_modal');
        $this->dispatch('toast', [
            'type' => 'success',
            'message' => __('toasts/toasts.application_accepted'),
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
        <div class="flex flex-col md:flex-row md:items-center lg:grid lg:grid-cols-12 mb-6 gap-6">
            <div class="flex flex-col md:flex-row items-center gap-4 md:items-center md:shrink-0 lg:col-span-3">
                <x-user-avatar
                    :user="$this->candidate->user"
                    preset="modal-preview"
                    class="w-full h-auto md:w-20 md:h-20 object-cover"
                />
                <div class="flex flex-col gap-1 text-center md:text-left">
                    <h3 class="text-2xl text-gold font-bold">{{ $this->candidate->user->username }}</h3>
                    <p class="text-text-gray">{{ $this->candidate->user->riot_tag }}</p>
                </div>
            </div>
            <div class="flex flex-wrap justify-around gap-6 md:flex-1 lg:contents" role="list">
                <div role="listitem" class="text-center lg:col-span-3 flex flex-col gap-1">
                    <p class="text-text-gray">{{ __('modals/team-application.desired_role') }}</p>
                    <div class="text-xl font-bold text-white flex justify-center items-center gap-2">
                        @if ($candidate->roleInTeam === RoleInTeam::COACH || $candidate->roleInTeam === RoleInTeam::STAFF)
                        <p>{{ $candidate->roleInTeam->label() }}</p>
                        @else
                        <img src="{{ asset($this->candidate->roleInGame->icon()) }}" class="w-8 h-8" alt="{{ $this->candidate->roleInGame->label() }}">
                        <p>{{ $this->candidate->roleInGame->label() }}</p>
                        @endif
                    </div>
                </div>
                <div role="listitem" class="text-center lg:col-span-3 flex flex-col gap-1">
                    <p class="text-text-gray">{{ __('modals/team-application.current_rank') }}</p>
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
                <div role="listitem" class="text-center lg:col-span-3 flex flex-col gap-1">
                    <p class="text-text-gray">{{ __('modals/team-application.winrate_label') }}</p>
                    <div class="flex flex-col gap-0.5">
                        <p class="text-gold text-xl font-bold">
                            @if ($this->candidate->user->riotProfile)
                            {{ $this->candidate->user->riotProfile->getWinratePercentage() }}%
                            @else
                            -
                            @endif
                        </p>
                        <p class="text-text-white text-sm">
                            @if ($this->candidate->user->riotProfile)
                            {{ __('modals/team-application.win_loss_format', ['wins' => $this->candidate->user->riotProfile->wins, 'losses' => $this->candidate->user->riotProfile->losses]) }}
                            @else
                            -
                            @endif
                        </p>
                    </div>

                </div>
            </div>
        </div>
        <div class="flex flex-col gap-2 mb-6">
            <h3 class="text-gold font-bold text-2xl">{{ __('modals/team-application.motivation_heading') }}</h3>
            @if ($this->candidate->motivation)
            <p class="text-white">{{ $this->candidate->motivation }}</p>
            @else
            <p class="text-text-secondary">{{ __('modals/team-application.no_motivation') }}</p>
            @endif
        </div>
        @if ($this->candidate->roleInTeam === RoleInTeam::PLAYER)
        @can('manageTeam', User::class)
        <div class="border-t-2 border-gray-500 pt-6">
            <fieldset>
                <legend class="mb-2 text-gold font-bold text-xl lg:text-2xl">
                    {{ __('modals/team-application.management_legend') }}
                </legend>
                <div class="grid gap-4">
                    <div>
                        <x-forms.radio
                            name="is_starter"
                            label="{{ __('modals/team-application.status_label') }}"
                            wire:model.live="is_starter"
                            :columns="2"
                            :required="true"
                            :options="[
                            ['value' => '1', 'label' => __('modals/team-application.starter')],
                            ['value' => '0', 'label' => __('modals/team-application.substitute')],
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
                        {{ __('modals/promote-to-starter.conflict_notice', [
                            'existing_username' => $this->existingStarterRoleInGame->user->username,
                            'role_label' => $this->existingStarterRoleInGame->roleInGame->label(),
                            'member_username' => $this->candidate->user->username,
                        ]) }}
                    </p>
                </div>
                @endif
                <div class="flex justify-between gap-2 mt-6">
                    <button wire:click="refuse" class="cta-secondary">
                        {{ __('modals/team-application.refuse') }}
                    </button>
                    <button wire:click="accept" class="cta-primary">
                        {{ __('modals/team-application.accept') }}
                    </button>
                </div>
            </fieldset>
        </div>
        @endcan
        @elseif ($this->candidate->roleInTeam !== RoleInTeam::PLAYER)
        @can('manageTeam', User::class)
        <div class="flex justify-between gap-2">
            <x-destructive wire:click="refuse">
                {{ __('modals/team-application.refuse') }}
            </x-destructive>
            <x-accept wire:click="accept">
                {{ __('modals/team-application.accept') }}
            </x-accept>
        </div>
        @endcan
        @endif
</div>
</x-layout.head-modal>
</div>