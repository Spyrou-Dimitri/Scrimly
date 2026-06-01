<?php

use Livewire\Attributes\Computed;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\User;
use App\Livewire\Forms\EditProfilForm;
use App\Enums\DefaultAvatar;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\RateLimiter;

new class extends Component
{
    use WithFileUploads;

    public User $currentUser;

    public EditProfilForm $form;

    public bool $preset_selected = true;

    public function rendering(View $view): void
    {
        $view->layout(
            Auth::user()->current_team_id ? 'layouts::team' : 'layouts::choose_a_team'
        );
    }

    public function mount(): void
    {
        $this->currentUser = Auth::user();
        $this->form->username = $this->currentUser->username;
        $this->form->email = $this->currentUser->email;
        $this->form->riot_tag = $this->currentUser->riot_tag;

        $this->form->default_avatar = $this->currentUser->avatar_type === 'default'
            ? $this->currentUser->avatar_value
            : DefaultAvatar::CAMILLE->value;

        $this->preset_selected = $this->currentUser->avatar_type !== 'upload';
    }

    public function updated($name): void
    {
        if ($name !== 'form.avatar') {
            return;
        }

        if ($this->form->avatar) {
            $this->preset_selected = false;
        }
    }

    public function choosePresetAvatar(string $value): void
    {
        $this->form->default_avatar = $value;
        $this->form->avatar = null;
        $this->preset_selected = true;
    }

    public function clearTemporaryAvatarUpload(): void
    {
        $this->form->avatar = null;
    }

    public function updateProfil(): void
    {
        $keyForRateLimiter = 'update-profil-'.Auth::user()->id;
        
        if (RateLimiter::tooManyAttempts($keyForRateLimiter, 3)) {
            $this->dispatch('toast', [
                'type' => 'error',
                'message' => __('toasts/toasts.too_many_attempts'),
            ]);
            return;
        }
        RateLimiter::hit($keyForRateLimiter, 60);

        $this->form->edit($this->preset_selected);
        $this->syncAvatarInputsFromStoredUser();

        $this->dispatch('toast', [
            'type' => 'success',
            'message' => __('toasts/toasts.profil_updated'),
        ]);
    }

    protected function syncAvatarInputsFromStoredUser(): void
    {
        $this->currentUser->refresh();

        $this->form->avatar = null;
        $this->form->default_avatar = $this->currentUser->avatar_type === 'default'
            ? $this->currentUser->avatar_value
            : DefaultAvatar::CAMILLE->value;
        $this->preset_selected = $this->currentUser->avatar_type !== 'upload';
    }

    public function openChangePasswordModal(): void
    {
        $this->dispatch('open_modal', [
            'form' => 'change-password',
            'model_id' => $this->currentUser->id,
        ]);
    }

    #[Computed]
    public function profileAvatarPreviewUrl(): string
    {
        if ($this->form->avatar) {
            return $this->form->avatar->temporaryUrl();
        }

        if (! $this->preset_selected && $this->currentUser->avatar_type === 'upload') {
            return $this->currentUser->avatar_url;
        }

        return DefaultAvatar::from($this->form->default_avatar)->url();
    }
};
?>

<div>
    <section class="flex flex-col gap-8">
        <h2 class="text-[32px] font-bold ">
            {{ __('profil/profil.title') }}
        </h2>
        <form wire:submit="updateProfil" class="grid grid-cols-1 gap-6 md:grid-cols-12">
            <fieldset class="avatar-fieldset m-0 flex min-w-0 flex-col gap-4 border-0 bg-bg-widget p-6 shadow-basic md:col-span-4 md:pl-8">
                <legend class="sr-only">
                    {{ __('register/register.avatar_section_title') }}
                </legend>
                <div class="flex flex-col gap-2">
                    <p class="w-full text-center text-xl font-bold text-gold lg:text-2xl">{{ __('register/register.avatar_section_title') }}</p>
                    <p class="text text-center text-text-secondary">{{ __('register/register.avatar_section_description') }}</p>
                </div>
                <div class="flex flex-col gap-4">
                    <div class="relative mx-auto flex w-full max-w-44 flex-col gap-3">
                        @if ($form->avatar)
                        <x-destructive
                            wire:click="clearTemporaryAvatarUpload"
                            type="button"
                            class="absolute -top-2 -right-2 z-[1]"
                            :only-icon="true">
                            <flux:icon name="trash" class="size-5 shrink-0 opacity-70" />
                        </x-destructive>
                        @endif
                        <div class="relative aspect-square w-full overflow-hidden rounded-lg bg-input-bg ring-2 ring-input-border">
                            <div wire:loading wire:target="form.avatar" class="pointer-events-none absolute inset-0 z-[1] flex flex-col items-center justify-center gap-2 bg-black/40 text-white">
                                <svg class="size-10 animate-spin opacity-90" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span class="sr-only">{{ __('register/register.upload_photo') }}</span>
                            </div>
                            <img
                                src="{{ $this->profileAvatarPreviewUrl }}"
                                alt="{{ __('register/register.avatar_section_image') }}"
                                class="absolute inset-0 size-full object-cover"
                                width="320"
                                height="320"
                                loading="lazy">
                        </div>
                    </div>
                    <div class="mx-auto w-fit">
                        <label
                            for="profileAvatarUpload"
                            class="cta-secondary relative focus-within:ring-2 focus-within:ring-gold-light flex cursor-pointer gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <polyline points="17 8 12 3 7 8"></polyline>
                                <line x1="12" y1="3" x2="12" y2="15"></line>
                            </svg>
                            {{ __('register/register.upload_photo') }}
                            <input wire:model="form.avatar" type="file" accept="image/*" id="profileAvatarUpload" class="absolute inset-0 cursor-pointer opacity-0">
                        </label>
                        @error('form.avatar')
                        <span class="font-spaceGrotesk font-semibold text-input-error">
                            {{ $message }}
                        </span>
                        @enderror
                        @error('form.default_avatar')
                        <span class="font-spaceGrotesk font-semibold text-input-error">
                            {{ $message }}
                        </span>
                        @enderror
                    </div>
                </div>
                <div class="flex flex-col gap-2">
                    <p class="text-center text-sm font-medium text-white">{{ __('register/register.choose_avatar') }}</p>
                    <div class="grid grid-cols-3 gap-3">
                        @foreach (DefaultAvatar::cases() as $avatar)
                        <div class="relative">
                            <button
                                wire:click="choosePresetAvatar('{{ $avatar->value }}')"
                                type="button"
                                title="{{ $avatar->label() }}"
                                @class([
                                    'block w-full cursor-pointer overflow-hidden rounded-lg transition-all hover:ring-gold-light focus:outline-none focus-visible:ring-2 focus-visible:ring-gold-light',
                                    'ring-2 ring-gold' => $form->default_avatar === $avatar->value,
                                    'ring-2 ring-transparent' => $form->default_avatar !== $avatar->value,
                                ])>
                                <img
                                    src="{{ $avatar->url() }}"
                                    alt="{{ $avatar->label() }}"
                                    class="aspect-square w-full object-cover">
                            </button>
                        </div>
                        @endforeach
                    </div>
                </div>
            </fieldset>
            <fieldset class="m-0 flex flex-col self-start gap-4 border-0 bg-bg-widget p-6 shadow-basic md:col-span-8 justify-between">
                <legend class="sr-only">
                    {{ __('profil/profil.information_account') }}
                </legend>
                <div class="bg-bg-widget flex flex-col gap-4">
                    <x-forms.input wire:model.live="form.username" :label="__('profil/profil.username')" :type="'text'" :name="'username'" :id="'username'" :placeholder="__('profil/profil.username')">
                        @error('form.username')
                        <span class="font-spaceGrotesk text-input-error font-semibold">
                            {{ $message }}
                        </span>
                        @enderror
                    </x-forms.input>
                    <x-forms.input wire:model.live="form.email" :label="__('profil/profil.email')" :type="'email'" :name="'email'" :id="'email'" :placeholder="'john.doe@example.com'">
                        @error('form.email')
                        <span class="font-spaceGrotesk text-input-error font-semibold">
                            {{ $message }}
                        </span>
                        @enderror
                    </x-forms.input>
                    <x-forms.input wire:model.live="form.riot_tag" :label="__('profil/profil.riot_tag')" :type="'text'" :name="'riot_tag'" :id="'riot_tag'" :placeholder="'HideOnBush#KR'">
                        @error('form.riot_tag')
                        <span class="font-spaceGrotesk text-input-error font-semibold">
                            {{ $message }}
                        </span>
                        @enderror
                    </x-forms.input>
                </div>
                <div class="flex col-span-full flex-col sm:flex-row justify-between gap-4">
                    <button class="cta-secondary" title="{{ __('profil/profil.change_password_title') }}" type="button" wire:click="openChangePasswordModal">
                        {{ __('profil/profil.change_password') }}
                    </button>
                    <x-forms.submit type="submit" variant="primary" :title="__('profil/profil.save')" class="w-full sm:w-fit" data-test="create-team-button">
                        {{ __('profil/profil.save') }}
                    </x-forms.submit>
                </div>
            </fieldset>


        </form>
    </section>
</div>