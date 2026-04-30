<?php

namespace App\Livewire\Forms;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Validate;
use Livewire\Form;

class ChangePasswordForm extends Form
{
    public User $user;

    #[Validate('required|current_password', as: 'modals/change-password.old_password')]
    public string $old_password = '';

    #[Validate('required|min:6|confirmed', as: 'modals/change-password.new_password')]
    public string $new_password = '';

    #[Validate('required', as: 'modals/change-password.confirm_password')]
    public string $new_password_confirmation = '';

    protected function validationAttributes()
    {
        return [
            'old_password' => 'Ancien mot de passe',
            'new_password' => 'Nouveau mot de passe',
            'new_password_confirmation' => 'Confirmer le nouveau mot de passe',
        ];
    }

    protected function messages()
    {
        return [
            'old_password.current_password' => 'L\'ancien mot de passe est incorrect.',
        ];
    }

    public function updatePassword(): void
    {
        $this->validate();

        $this->user->password = Hash::make($this->new_password);
        $this->user->save();
    }
}
