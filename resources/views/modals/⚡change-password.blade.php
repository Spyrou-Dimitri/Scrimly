<?php

use App\Models\User;
use Livewire\Component;
use App\Livewire\Forms\ChangePasswordForm;

new class extends Component {
    public ChangePasswordForm $form;
    public function mount($model_id)
    {
        $this->form->user = User::findOrFail($model_id);
    }

    public function update_password(): void
    {
        $this->form->updatePassword();
        $this->dispatch('close_modal');
    }
}
?>



<x-layout.head-modal :title="__('modals/change-password.title')">
    <form class="flex flex-col gap-4" wire:click.stop wire:submit.prevent="update_password">
        <fieldset class="flex flex-col justify-center gap-4">
            <legend class="sr-only">
                <span>{{ __('modals/change-password.legend_form') }}</span>
            </legend>
            <x-forms.input placeholder="*******" wire:model.live="form.old_password" :name="'old-password'"
                :placeholder="'*******'"
                :type="'password'" :label="__('modals/change-password.old_password')">
                <span class="font-poppins text-red-600 font-semibold">
                    @error('form.old_password') {{ $message }} @enderror
                </span>
            </x-forms.input>
            <x-forms.input wire:model.blur="form.new_password" :name="'new-password'" :placeholder="'*******'"
                :type="'password'"
                :label="__('modals/change-password.new_password')">
                <span class="font-poppins text-red-600 font-semibold">
                    @error('form.new_password') {{ $message }} @enderror
                </span>
            </x-forms.input>
            <x-forms.input wire:model.blur="form.new_password_confirmation" :name="'new-password-confirm'"
                :placeholder="'*******'"
                :type="'password'" :label="__('modals/change-password.confirm_password')">
                <span class="font-poppins text-red-600 font-semibold">
                    @error('form.new_password_confirmation') {{ $message }} @enderror
                </span>
            </x-forms.input>
            <x-forms.submit>
                {{ __('modals/change-password.save') }}
            </x-forms.submit>
        </fieldset>
    </form>
</x-layout.head-modal>