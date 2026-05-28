<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Enums\RoleInTeam;
use App\Enums\RoleInGame;
use App\Livewire\Forms\CreateInvitationTeamForm;
use App\Models\User;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Gate;
use App\Models\TeamInvitation;

new #[Layout('layouts::team')] class extends Component
{
    public CreateInvitationTeamForm $form;
    public function mount(): void
    {
        abort_if(Gate::denies('manageTeam', User::class), 403, __('policies/roster.error_manage_roster'));
    }
    #[Computed]
    public function userFinder(): User|null
    {
        return User::where('username', $this->form->username)->first();
    }
    public function inviteUser(): void
    {
        $this->form->validate();
        if (!$this->userFinder) {
            return;
        }

        $authorization = Gate::inspect('create', [TeamInvitation::class, currentTeam(), $this->userFinder]);
        if ($authorization->denied()) {
            $this->dispatch('toast', [
                'title' => __('policies/roster.error_title'),
                'message' => $authorization->message(),
                'type' => 'error',
            ]);

            return;
        }
        if (! $this->form->store(currentTeam(), $this->userFinder)) {
            $this->dispatch('toast', [
                'title' => __('policies/roster.error_title'),
                'message' => $this->form->getErrorBag()->first('error'),
                'type' => 'error',
            ]);

            return;
        }

        session()->flash('toast', [
            'type' => 'success',
            'message' => __('toasts/toasts.invitation_sent'),
        ]);

        $this->redirect(route('roster.index', ['slug' => currentTeam()->slug]));
    }
};
?>

<div>
    <section class="flex flex-col gap-8">
        <h2 class="text-[32px] font-bold ">
            {{ __('pages/roster/invitations.title') }}
        </h2>
        <form wire:submit="inviteUser" class="grid grid-cols-1 gap-6 lg:grid-cols-12">
            <fieldset class="flex flex-col gap-4 bg-bg-widget p-6 shadow-basic lg:col-span-8">
                <legend class="sr-only">
                    {{ __('pages/roster/invitations.form_legend') }}
                </legend>
                <div class="flex flex-col gap-4">
                    <div class="flex flex-col gap-4 sm:flex-row sm:gap-6">
                        <x-forms.input wire:model.live="form.username" :required="true" :placeholder="__('pages/roster/invitations.username_placeholder')" :type="'text'" :name="'team_code'" class="w-full" :label="__('pages/roster/invitations.username')">
                            @error('form.username')
                            <span class="font-spaceGrotesk text-input-error font-semibold">
                                {{ $message }}
                            </span>
                            @enderror
                        </x-forms.input>
                        <x-forms.select wire:model.live="form.roleInTeam" :required="true" :disabled="__('pages/roster/invitations.roleInTeam_disabled')" :name="'roleInTeam'" :label="__('pages/roster/invitations.roleInTeam')" :options="RoleInTeam::cases()">
                            @error('form.roleInTeam')
                            <span class="font-spaceGrotesk text-input-error font-semibold">
                                {{ $message }}
                            </span>
                            @enderror
                        </x-forms.select>
                        @if($form->roleInTeam === RoleInTeam::PLAYER)
                        <x-forms.select wire:model.live="form.roleInGame" :required="true" :disabled="__('pages/roster/invitations.roleInGame_disabled')" :name="'roleInGame'" :label="__('pages/roster/invitations.roleInGame')" :options="RoleInGame::cases()">
                            @error('form.roleInGame')
                            <span class="font-spaceGrotesk text-input-error font-semibold">
                                {{ $message }}
                            </span>
                            @enderror
                        </x-forms.select>
                        @endif
                    </div>
                    <div class="flex flex-col gap-2">
                        <x-forms.textarea wire:model.live="form.motivation" :placeholder="__('pages/team/join.motivation_placeholder')" :name="'motivation'" :label="__('pages/team/join.motivation')" />
                        @error('form.description')
                        <span class="font-spaceGrotesk text-input-error font-semibold">
                            {{ $message }}
                        </span>
                        @enderror
                    </div>
                    <div class="flex col-span-full flex-row justify-between gap-4">
                        <x-cta :href="route('team.index')" class="secondary" :title="__('pages/team/create.cancel')">
                            {{ __('pages/team/join.cancel') }}
                        </x-cta>
                        <x-forms.submit type="submit" variant="primary" :title="__('pages/team/create.create')" class="w-fit" data-test="create-team-button">
                            {{ __('pages/roster/invitations.invite') }}
                        </x-forms.submit>
                    </div>
                </div>
            </fieldset>
            <div class="flex flex-col gap-4 bg-bg-widget p-6 shadow-basic lg:col-span-4 lg:self-start w-full">
                <h3 class="text-[20px] font-bold text-center">
                    @if($this->userFinder)
                    {{ __('pages/roster/invitations.user_selected') }}
                    @else
                    {{ __('pages/roster/invitations.user_not_found') }}
                    @endif
                </h3>
                @if($this->userFinder)
                <x-user-avatar
                    :user="$this->userFinder"
                    preset="join-preview"
                    class="px-16 w-full  object-fit" />

                @else
                <div class="px-16 py-4 w-full h-auto object-fit">
                    <x-flux::icon name="eye-slash" class="size-full text-gold" />
                </div>
                @endif
                @if($this->userFinder)
                <div class="flex flex-col gap-2">
                    <p class="text-center text-2xl font-bold text-gold">
                        {{ $this->userFinder->username }}
                    </p>
                    <p class="text-center text-sm text-text-secondary">
                        {{ $this->userFinder->riotProfile->riot_tag }}
                    </p>
                    <p class="text-center text-xl text-white">
                        {{ $this->userFinder->riotProfile->tier->label() }} {{ $this->userFinder->riotProfile->rank }} • {{ $this->userFinder->riotProfile->lp }} LP
                    </p>
                </div>

                @endif
            </div>
        </form>
    </section>
</div>