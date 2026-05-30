<?php

use App\Enums\DefaultTeam;
use App\Enums\Language;
use App\Enums\LolGoal;
use App\Enums\LolServeur;
use App\Livewire\Forms\EditTeamForm;
use App\Models\Team;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\User;
use Livewire\WithFileUploads;

new #[Layout('layouts::team')] class extends Component
{
    use WithFileUploads;

    public Team $team;

    public EditTeamForm $form;

    public bool $isChoosingPresetLogo = true;

    public function mount(string $slug, int|string $id): void
    {
        $contextTeam = currentTeam();

        if (! $contextTeam || $contextTeam->slug !== $slug) {
            abort(404);
        }

        $this->team = Team::query()->findOrFail($id);

        if ($this->team->id !== $contextTeam->id) {
            abort(404);
        }

        Gate::authorize('manageTeam', Auth::user());

        $this->form->setTeam($this->team);
        $this->isChoosingPresetLogo = $this->team->logo_type === 'default';
    }

    public function updated($name): void
    {
        if ($name !== 'form.logo') {
            return;
        }

        if ($this->form->logo) {
            $this->isChoosingPresetLogo = false;
        }
    }

    public function clearTemporaryTeamLogoUpload(): void
    {
        $this->form->logo = null;
    }

    public function choosePresetLogo(string $logo): void
    {
        $this->form->logo = null;
        $this->form->default_logo = $logo;
        $this->isChoosingPresetLogo = true;
    }

    #[Computed]
    public function previewPresetLogo(): string
    {
        if ($this->form->logo) {
            return $this->form->logo->temporaryUrl();
        }

        if (! $this->isChoosingPresetLogo && $this->team->logo_type === 'upload') {
            return $this->team->logo_url;
        }

        return DefaultTeam::from($this->form->default_logo)->url();
    }

    public function updateTeam(): void
    {
        $newSlug = $this->form->update($this->isChoosingPresetLogo);

        $this->team->refresh();
        $this->form->setTeam($this->team);
        $this->form->logo = null;
        $this->isChoosingPresetLogo = $this->team->logo_type === 'default';

        $this->dispatch('toast', [
            'type' => 'success',
            'message' => __('toasts/toasts.team_updated'),
        ]);

        $this->redirectRoute('team.show', [
            'slug' => $newSlug,
            'id' => $this->team->id,
        ]);
    }
};
?>

<div class="w-full max-w-[1600px] mx-auto">
    <section class="flex flex-col gap-8">
    <div class="flex items-center gap-4 justify-between">
            <h2 class="text-[32px] font-bold">
                {{ __('pages/team/edit.title') }}
            </h2>
            @can('manageTeam', User::class)
            <x-destructive type="button" wire:click="openModalDeleteTeam" :title="__('pages/team/edit.delete_team_title')">
                {{ __('pages/team/edit.delete_team_cta') }}
            </x-destructive>
            @endcan
        </div>
        <form wire:submit="updateTeam" class="flex flex-col gap-6 lg:grid lg:grid-cols-12 lg:items-start">
            <fieldset class="avatar-fieldset m-0 flex min-w-0 flex-col gap-4 border-0 bg-bg-widget p-6 shadow-basic lg:col-span-4 lg:col-start-1 lg:row-start-1 lg:row-span-2 lg:w-full">
                <legend class="sr-only">
                    {{ __('pages/team/edit.logo_section_title') }}
                </legend>
                <div class="flex flex-col gap-2">
                    <h3 class="w-full text-center text-2xl font-bold text-gold">{{ __('pages/team/edit.logo_section_title') }}</h3>
                    <p class="text-center text-text-secondary">{{ __('pages/team/edit.logo_section_description') }}</p>
                </div>
                <div class="flex flex-col gap-4">
                    <div class="relative mx-auto flex w-full max-w-44 flex-col gap-3">
                        @if ($form->logo)
                        <x-destructive
                            wire:click="clearTemporaryTeamLogoUpload"
                            type="button"
                            class="absolute -top-2 -right-2 z-[2]"
                            :only-icon="true">
                            <flux:icon name="trash" class="size-5 shrink-0 opacity-70" />
                        </x-destructive>
                        @endif
                        <div class="relative aspect-square w-full overflow-hidden rounded-lg bg-input-bg ring-2 ring-input-border">
                            <div wire:loading wire:target="form.logo" class="pointer-events-none absolute inset-0 z-[1] flex flex-col items-center justify-center gap-2 bg-black/40 text-white">
                                <svg class="size-10 animate-spin opacity-90" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span class="sr-only">{{ __('pages/team/edit.upload_logo') }}</span>
                            </div>
                            @if (!$form->logo && !$isChoosingPresetLogo && $team->logo_type !== 'upload')
                            <div class="absolute inset-0 flex flex-col items-center justify-center gap-3 px-4 text-center text-text-secondary">
                                <flux:icon name="photo" class="size-12 shrink-0 opacity-70" />
                                <p class="text-xs leading-snug font-medium">{{ __('pages/team/edit.logo_preview_placeholder') }}</p>
                            </div>
                            @else
                            <img
                                src="{{ $this->previewPresetLogo }}"
                                alt="{{ __('pages/team/edit.logo_image_alt') }}"
                                class="absolute inset-0 size-full object-cover"
                                width="320"
                                height="320"
                                loading="lazy">
                            @endif
                        </div>
                    </div>
                    <div class="mx-auto w-fit">
                        <label
                            for="teamLogoUpload"
                            class="cta-secondary relative focus-within:ring-2 focus-within:ring-gold-light flex cursor-pointer gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <polyline points="17 8 12 3 7 8"></polyline>
                                <line x1="12" y1="3" x2="12" y2="15"></line>
                            </svg>
                            {{ __('pages/team/edit.upload_logo') }}
                            <input wire:model="form.logo" type="file" accept="image/*" id="teamLogoUpload" name="logo" class="absolute inset-0 cursor-pointer opacity-0">
                        </label>
                        @error('form.logo')
                        <span class="font-spaceGrotesk font-semibold text-input-error">
                            {{ $message }}
                        </span>
                        @enderror
                    </div>
                </div>
                <div class="flex flex-col gap-2">
                    <p class="text-center text-sm font-medium text-white">{{ __('pages/team/edit.choose_logo_preset') }}</p>
                    <div class="grid grid-cols-3 sm:grid-cols-6 lg:grid-cols-3 gap-3 select-none" aria-hidden="true">
                        @foreach (DefaultTeam::cases() as $logo)
                        <div class="relative">
                            <button
                                wire:click="choosePresetLogo('{{ $logo->value }}')"
                                type="button"
                                title="{{ $logo->label() }}"
                                @class([ 'block w-full cursor-pointer overflow-hidden rounded-lg transition-all hover:ring-gold-light focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-light' , 'ring-2 ring-gold'=> $form->default_logo === $logo->value,
                                'ring-2 ring-transparent' => $form->default_logo !== $logo->value,
                                ])>
                                <img
                                    src="{{ $logo->url() }}"
                                    alt="{{ $logo->label() }}"
                                    class="aspect-square w-full object-cover">
                            </button>
                        </div>
                        @endforeach
                    </div>
                </div>
            </fieldset>
            <div class="flex min-w-0 flex-col gap-6 border-0 bg-bg-widget p-6 shadow-basic lg:col-span-8 lg:col-start-5 lg:row-start-1 lg:row-span-2 lg:pr-8">
                <fieldset class="m-0 flex flex-col gap-6 border-0 bg-transparent p-0 shadow-none">
                    <legend class="sr-only">
                        {{ __('pages/team/edit.information_section_title') }}
                    </legend>
                    <div class="hidden flex-col gap-2 lg:flex">
                        <h3 class="text-2xl font-bold text-gold">
                            {!! __('pages/team/edit.information_section_title') !!}
                        </h3>
                        <p class="text-text-secondary">
                            {{ __('pages/team/edit.slogan') }}
                        </p>
                    </div>
                    <div class="flex flex-col gap-4">
                        <x-forms.input wire:model.live="form.team_name" :required="true" :placeholder="__('pages/team/edit.team_name_placeholder')" :type="'text'" :name="'team_name'" class="w-full" :label="__('pages/team/edit.team_name')">
                            @error('form.team_name')
                            <span class="font-spaceGrotesk text-input-error font-semibold">
                                {{ $message }}
                            </span>
                            @enderror
                        </x-forms.input>
                        <div class="flex flex-col gap-4 sm:flex-row sm:gap-6">
                            <x-forms.select wire:model.live="form.server" :required="true" :disabled="'-- Sélectionnez le serveur --'" :name="'server'" :label="__('pages/team/edit.server')" :options="LolServeur::cases()">
                                @error('form.server')
                                <span class="font-spaceGrotesk text-input-error font-semibold">
                                    {{ $message }}
                                </span>
                                @enderror
                            </x-forms.select>
                            <x-forms.select wire:model.live="form.goal" :required="true" :name="'goal'" :label="__('pages/team/edit.goal')" :options="LolGoal::cases()" :disabled="'-- Quel est le but de votre équipe ? --'">
                                @error('form.goal')
                                <span class="font-spaceGrotesk text-input-error font-semibold">
                                    {{ $message }}
                                </span>
                                @enderror
                            </x-forms.select>
                        </div>
                        <x-forms.select wire:model.live="form.language" :required="true" :disabled="'-- Sélectionnez la langue --'" :name="'language'" :label="__('pages/team/edit.language')" :options="Language::cases()">
                            @error('form.language')
                            <span class="font-spaceGrotesk text-input-error font-semibold">
                                {{ $message }}
                            </span>
                            @enderror
                        </x-forms.select>
                        <div class="flex flex-col gap-2">
                            <x-forms.textarea rows="8" wire:model.live="form.description" :placeholder="__('pages/team/edit.description_placeholder')" :name="'description'" :label="__('pages/team/edit.description')" />
                            @error('form.description')
                            <span class="font-spaceGrotesk text-input-error font-semibold">
                                {{ $message }}
                            </span>
                            @enderror
                        </div>
                    </div>
                    <div class="flex flex-row justify-between gap-4">
                        <x-cta :href="route('team.show', ['slug' => currentTeam()->slug, 'id' => $team->id])" class="secondary" :title="__('pages/team/edit.cancel')">
                            {{ __('pages/team/edit.cancel') }}
                        </x-cta>
                        <x-forms.submit type="submit" variant="primary" :title="__('pages/team/edit.save')" class="w-fit" data-test="update-team-button">
                            {{ __('pages/team/edit.save') }}
                        </x-forms.submit>
                    </div>
                </fieldset>
            </div>
        </form>
    </section>
</div>
