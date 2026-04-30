<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\User;
use App\Livewire\Forms\EditProfilForm;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;
    public User $currentUser;

    public EditProfilForm $form;

    public function rendering(View $view)
    {
        $view->layout(
            Auth::user()->current_team_id ? 'layouts::team' : 'layouts::choose_a_team'
        );
    }
    public function mount()
    {
        $this->currentUser = Auth::user();
        $this->form->username = $this->currentUser->username;
        $this->form->email = $this->currentUser->email;
        $this->form->riot_tag = $this->currentUser->riot_tag;
    }

    public function updateProfil(): void
    {
        $this->form->edit();
        $this->dispatch('toast', [
            'type' => 'success',
            'message' => __('toasts/toasts.profil_updated'),
        ]);
    }
    public function openChangePasswordModal(): void
    {
        $this->dispatch('open_modal', [
            'form' => 'change-password',
            'model_id' => $this->currentUser->id,
        ]);
    }
};
?>

<div>
    <section class="flex flex-col gap-8">
        <h2 class="text-[32px] font-bold ">
            {{ __('profil/profil.title') }}
        </h2>
        <form wire:submit="updateProfil" class="grid grid-cols-1 gap-6 lg:grid-cols-12">
            <fieldset class="flex flex-col gap-4 bg-bg-widget p-6 shadow-basic lg:col-span-8 justify-between">
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
                        @error('form.password')
                        <span class="font-spaceGrotesk text-input-error font-semibold">
                            {{ $message }}
                        </span>
                        @enderror
                    </x-forms.input>
                </div>
                <div class="flex col-span-full flex-row justify-between gap-4">
                    <button class="cta-secondary" title="{{ __('profil/profil.change_password_title') }}" wire:click="openChangePasswordModal">
                        {{ __('profil/profil.change_password') }}
                    </button>
                    <x-forms.submit type="submit" variant="primary" :title="__('profil/profil.save')" class="w-fit" data-test="create-team-button">
                        {{ __('profil/profil.save') }}
                    </x-forms.submit>
                </div>
            </fieldset>
            <fieldset class="flex flex-col gap-4 bg-bg-widget p-6 shadow-basic lg:col-span-4 w-full">
                <legend class="sr-only">
                    Avatar
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
                        @if($form->avatar)
                        <img src="{{ $form->avatar->temporaryUrl() }}" alt="Avatar preview" class="w-full aspect-square object-cover h-auto" />
                        @elseif($currentUser->avatar)
                        <img src="{{ Storage::disk('public')->url('images/avatar/variants/480x480/' . $currentUser->avatar) }}" alt="Avatar" class="w-full aspect-square object-cover h-auto" />
                        @else
                        <flux:icon.user class="w-10 h-10" />
                        @endif
                        {{ __('profil/profil.avatar') }}
                    </label>
                    @error('form.avatar')
                    <span class="font-spaceGrotesk text-input-error font-semibold">
                        {{ $message }}
                    </span>
                    @enderror

                    <input
                        wire:model.live="form.avatar"
                        type="file"
                        name="logo"
                        id="logo"
                        @focus="focused = true"
                        @blur="focused = false"
                        class="absolute inset-0 opacity-0 cursor-pointer" />
                </div>
            </fieldset>

        </form>
    </section>
</div>