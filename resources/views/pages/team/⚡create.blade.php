<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Enums\LolServeur;
use App\Enums\LolGoal;
use App\Enums\Language;
use App\Enums\RoleInTeam;
use App\Enums\RoleInGame;
use App\Livewire\Forms\CreateTeamForm;
use Livewire\WithFileUploads;

new #[Layout('layouts::choose_a_team')] class extends Component {

    public CreateTeamForm $form;

    use WithFileUploads;

    public function createTeam(): void
    {
        $this->form->store();

        session()->flash('toast', [
            'type' => 'success',
            'message' => $this->form->team_name . ' ' . __('toasts/toasts.team_created'),
        ]);

        $this->redirect(route('team.index'));
    }
};

?>

<div class="w-full max-w-[1600px] mx-auto">
    <section class="flex flex-col gap-8">
        <h2 class="text-[32px] font-bold ">
            {{ __('pages/team/create.title') }}
        </h2>
        <form wire:submit="createTeam" class="grid grid-cols-1 gap-6 lg:grid-cols-12">
            <fieldset class="flex flex-col gap-4 bg-bg-widget p-6 shadow-basic lg:col-span-8">
                <legend class="sr-only">
                    Informations de l'équipe
                </legend>
                <div class="flex flex-col gap-4">
                    <div class="flex flex gap-6">
                        <x-forms.input wire:model.live="form.team_name" :required="true" :placeholder="__('pages/team/create.team_name_placeholder')" :type="'text'" :name="'team_name'" class="w-full" :label="__('pages/team/create.team_name')">
                            @error('form.team_name')
                            <span class="font-spaceGrotesk text-input-error font-semibold">
                                {{ $message }}
                            </span>
                            @enderror
                        </x-forms.input>
                        <x-forms.input wire:model.live="form.tag" :required="true" :placeholder="__('pages/team/create.team_tag_placeholder')" :type="'text'" :name="'tag'" class="w-full" :label="__('pages/team/create.team_tag')">
                            @error('form.tag')
                            <span class="font-spaceGrotesk text-input-error font-semibold">
                                {{ $message }}
                            </span>
                            @enderror
                        </x-forms.input>
                    </div>
                    <div class="flex flex-row gap-6">
                        <x-forms.select wire:model.live="form.server" :required="true" :disabled="'-- Sélectionnez le serveur --'" :name="'server'" :label="__('pages/team/create.server')" :options="LolServeur::cases()">
                            @error('form.server')
                            <span class="font-spaceGrotesk text-input-error font-semibold">
                                {{ $message }}
                            </span>
                            @enderror
                        </x-forms.select>
                        <x-forms.select wire:model.live="form.goal" :required="true" :name="'goal'" :label="__('pages/team/create.goal')" :options="LolGoal::cases()" :disabled="'-- Quel est le but de votre équipe ? --'">
                            @error('form.goal')
                            <span class="font-spaceGrotesk text-input-error font-semibold">
                                {{ $message }}
                            </span>
                            @enderror
                        </x-forms.select>
                    </div>
                    <div class="flex flex-row gap-6">
                        <x-forms.select wire:model.live="form.language" :required="true" :disabled="'-- Sélectionnez la langue --'" :name="'language'" :label="__('pages/team/create.language')" :options="Language::cases()">
                            @error('form.language')
                            <span class="font-spaceGrotesk text-input-error font-semibold">
                                {{ $message }}
                            </span>
                            @enderror
                        </x-forms.select>
                        <x-forms.select wire:model.live="form.roleInTeam" :required="true" :disabled="'-- Sélectionnez le rôle --'" :name="'roleInTeam'" :label="__('pages/team/create.roleInTeam')" :options="RoleInTeam::cases()">
                            @error('form.roleInTeam')
                            <span class="font-spaceGrotesk text-input-error font-semibold">
                                {{ $message }}
                            </span>
                            @enderror
                        </x-forms.select>
                        @if($form->roleInTeam === RoleInTeam::PLAYER)
                        <x-forms.select wire:model.live="form.roleInGame" :required="true" :disabled="'-- Sélectionnez le rôle --'" :name="'roleInGame'" :label="__('pages/team/create.roleInGame')" :options="RoleInGame::cases()">
                            @error('form.roleInGame')
                            <span class="font-spaceGrotesk text-input-error font-semibold">
                                {{ $message }}
                            </span>
                            @enderror
                        </x-forms.select>
                        @endif
                    </div>
                    <div class="flex flex-col gap-2">
                        <x-forms.textarea wire:model.live="form.description" :placeholder="__('pages/team/create.description_placeholder')" :name="'description'" :label="__('pages/team/create.description')" />
                        @error('form.description')
                        <span class="font-spaceGrotesk text-input-error font-semibold">
                            {{ $message }}
                        </span>
                        @enderror
                    </div>
                </div>
            </fieldset>
            <fieldset class="flex flex-col gap-4 bg-bg-widget p-6 shadow-basic lg:col-span-4 w-full">
                <legend class="sr-only">
                    Logo
                </legend>
                <div
                    x-data="{ hovering: false, focused: false }"
                    @mouseenter="hovering = true"
                    @mouseleave="hovering = false"
                    :class="{
                    'border-gold': hovering || focused,
                    'border-transparent': !hovering && !focused
                    }"
                    class="relative min-h-full flex flex-col items-center justify-center 
                    bg-input-bg border transition-colors duration-200">
                    <label for="logo" class="font-medium flex flex-col items-center justify-center gap-2 pointer-events-none">
                        @if($form->logo)
                        <img src="{{ $form->logo->temporaryUrl() }}" alt="Logo" class="w-full h-auto" />
                        @else
                        <img src="{{ asset('icons/file.svg') }}" alt="Logo" class="w-full h-auto" />
                        @endif
                        {{ __('pages/team/create.logo') }}
                    </label>

                    <input
                        wire:model.live="form.logo"
                        type="file"
                        name="logo"
                        id="logo"
                        @focus="focused = true"
                        @blur="focused = false"
                        class="absolute inset-0 opacity-0 cursor-pointer" />
                </div>
                @error('form.logo')
                <span class="font-spaceGrotesk text-input-error font-semibold">
                    {{ $message }}
                </span>
                @enderror
            </fieldset>
            <div class="flex col-span-full flex-row justify-between gap-4 p-6 bg-bg-widget shadow-basic">
                <x-cta :href="route('team.index')" class="secondary" :title="__('pages/team/create.cancel')">
                    {{ __('pages/team/create.cancel') }}
                </x-cta>
                <x-forms.submit type="submit" variant="primary" :title="__('pages/team/create.create')" class="w-fit" data-test="create-team-button">
                    {{ __('pages/team/create.create') }}
                </x-forms.submit>
            </div>
        </form>
    </section>

</div>