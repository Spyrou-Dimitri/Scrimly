<?php

namespace App\Livewire\Forms;

use App\Jobs\ProcessUploadImageAvatar;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Form;

class EditProfilForm extends Form
{
    #[Validate]
    public string $username = '';

    #[Validate]
    public string $email = '';

    #[Validate]
    public ?string $riot_tag = '';

    #[Validate]
    public $avatar = null;

    protected function rules(): array
    {
        return [
            'username' => ['required', 'string', 'min:3', 'max:30', Rule::unique(User::class)->ignore(Auth::id())],
            'email' => ['required', 'email:rfc', 'regex:/^[^@\s]+@[^@\s]+\.[a-zA-Z]{2,}$/', Rule::unique(User::class)->ignore(Auth::id())],
            'riot_tag' => ['nullable', 'string', 'min:3', 'max:30'],
            'avatar_type' => ['nullable', 'string', 'in:upload,default'],
        ];
    }

    public function attributes(): array
    {
        return [
            'username' => 'pages/profile/edit.username',
            'email' => 'pages/profile/edit.email',
            'riot_tag' => 'pages/profile/edit.riot_tag',
            'avatar' => 'pages/profile/edit.avatar',
        ];
    }

    public function edit(): void
    {
        $validated = $this->validate();

        $user = User::where('id', Auth::id())->first();
        if ($validated['avatar']) {
            $extension = $validated['avatar']->extension() ?: $validated['avatar']->getClientOriginalExtension();
            $new_original_file_name = uniqid().'.'.$extension;
            $full_path_to_original = Storage::disk('public')->putFileAs(
                config('avatar.original_path'),
                $validated['avatar'],
                $new_original_file_name
            );
            if ($full_path_to_original) {
                $validated['avatar'] = $new_original_file_name;
                ProcessUploadImageAvatar::dispatchSync($full_path_to_original, $new_original_file_name);
            } else {
                $validated['avatar'] = '';
            }

            if ($user->avatar) {

                Storage::disk('public')->delete('images/avatar/variants/480x480/'.$user->avatar);
            }
        }

        $user->update([
            'username' => $validated['username'],
            'email' => $validated['email'],
        ]);

        if (blank($validated['riot_tag'])) {
            $user->riotProfile?->delete();
        } else {
            $user->riotProfile()->updateOrCreate(
                ['user_id' => $user->id],
                ['riot_tag' => $validated['riot_tag']]
            );
        }

        if ($validated['avatar']) {
            $user->avatar = $validated['avatar'];
            $user->save();
        }
    }
}
