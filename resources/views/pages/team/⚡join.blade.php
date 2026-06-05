<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Enums\LolServeur;
use App\Enums\LolGoal;
use App\Enums\Language;
use App\Enums\RoleInTeam;
use App\Enums\RoleInGame;
use App\Livewire\Forms\JoinTeamForm;
use Livewire\WithFileUploads;
use App\Models\Team;
use App\Models\TeamApplication;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Gate;

new #[Layout('layouts::choose_a_team')] class extends Component {
    public JoinTeamForm $form;

    public function joinTeam(): void
    {
        $this->form->validate();

        $authorization = Gate::inspect('canApplyForTeam', [TeamApplication::class, $this->teamFinder]);

        if ($authorization->denied()) {
            $this->dispatch('toast', [
                'title' => __('policies/roster.error_title'),
                'message' => $authorization->message(),
                'type' => 'error',
            ]);

            return;
        }

        if (! $this->form->store()) {
            $this->dispatch('toast', [
                'title' => __('policies/roster.error_title'),
                'message' => $this->form->getErrorBag()->first('error'),
                'type' => 'error',
            ]);

            return;
        }

        session()->flash('toast', [
            'type' => 'success',
            'message' => __('toasts/toasts.team_applied'),
        ]);

        $this->redirect(route('team.index'));
    }
    #[Computed]
    public function teamFinder(): Team|null
    {
        return Team::where('code', $this->form->team_code)->first();
    }
}

?>

<div class="w-full max-w-[1600px] mx-auto">
    <section class="flex flex-col gap-8" aria-labelledby="team-join-heading">
        <h2 id="team-join-heading" class="text-[32px] font-bold ">
            {{ __('pages/team/join.title') }}
        </h2>
        <form wire:submit="joinTeam" class="grid grid-cols-1 gap-6 lg:grid-cols-12">
            <fieldset class="flex flex-col gap-4 bg-bg-widget p-6 shadow-basic lg:col-span-8">
                <legend class="sr-only">
                    {{ __('pages/team/join.form_legend') }}
                </legend>
                <div class="flex flex-col gap-4">
                    <div class="flex flex-col gap-4 sm:flex-row sm:gap-6">
                        <x-forms.input wire:model.live="form.team_code" :required="true" :placeholder="__('pages/team/join.team_code_placeholder')" :type="'text'" :name="'team_code'" class="w-full" :label="__('pages/team/join.team_code')">
                            @error('form.team_code')
                            <span class="font-spaceGrotesk text-input-error font-semibold">
                                {{ $message }}
                            </span>
                            @enderror
                        </x-forms.input>
                        <x-forms.select wire:model.live="form.roleInTeam" :required="true" :disabled="'-- Sélectionnez le rôle --'" :name="'roleInTeam'" :label="__('pages/team/join.roleInTeam')" :options="RoleInTeam::cases()">
                            @error('form.roleInTeam')
                            <span class="font-spaceGrotesk text-input-error font-semibold">
                                {{ $message }}
                            </span>
                            @enderror
                        </x-forms.select>
                        @if($form->roleInTeam === RoleInTeam::PLAYER)
                        <x-forms.select wire:model.live="form.roleInGame" :required="true" :disabled="'-- Sélectionnez le rôle --'" :name="'roleInGame'" :label="__('pages/team/join.roleInGame')" :options="RoleInGame::cases()">
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
                            {{ __('pages/team/join.join') }}
                        </x-forms.submit>
                    </div>
                </div>
            </fieldset>
            <div class="flex flex-col gap-4 bg-bg-widget p-6 shadow-basic lg:col-span-4 w-full self-start">
                <h3 class="text-[20px] font-bold text-center">
                    @if($this->teamFinder)
                    Equipe sélectionnée :
                    @else
                    Equipe non trouvée
                    @endif
                </h3>
                @if($this->teamFinder)
                    <x-team-logo
                        :team="$this->teamFinder"
                        preset="join-preview"
                        class="px-16 py-4 w-full h-auto object-fit"
                    />
                    
                @else
                <div class="px-16 py-4 w-full h-auto object-fit">
                    <x-flux::icon name="eye-slash" class="size-full text-gold" />
                </div>
                @endif
                @if($this->teamFinder)
                <p class="text-center text-2xl font-bold text-gold">
                    {{ $this->teamFinder->name }}
                </p>
                @endif
            </div>
        </form>
    </section>
</div>