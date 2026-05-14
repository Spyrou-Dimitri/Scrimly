<?php

namespace App\Livewire\Forms;

use App\Enums\DefaultAvatar;
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

    #[Validate]
    public string $default_avatar = 'Camille';

    protected function rules(): array
    {
        return [
            'username' => ['required', 'string', 'min:3', 'max:30', Rule::unique(User::class)->ignore(Auth::id())],
            'email' => ['required', 'email:rfc', 'regex:/^[^@\s]+@[^@\s]+\.[a-zA-Z]{2,}$/', Rule::unique(User::class)->ignore(Auth::id())],
            'riot_tag' => ['nullable', 'string', 'min:3', 'max:30'],
            'avatar' => ['nullable', 'image', 'max:2048'],
            'default_avatar' => ['required', Rule::enum(DefaultAvatar::class)],
        ];
    }

    public function attributes(): array
    {
        return [
            'username' => 'pages/profile/edit.username',
            'email' => 'pages/profile/edit.email',
            'riot_tag' => 'pages/profile/edit.riot_tag',
            'avatar' => 'pages/profile/edit.avatar',
            'default_avatar' => 'pages/profile/edit.default_avatar',
        ];
    }

    
    public function edit(bool $applyPresetAvatar): void
    {
        $validated = $this->validate();

        $user = User::query()->findOrFail(Auth::id());

        $avatarType = $user->avatar_type;
        $avatarValue = $user->avatar_value;

        if ($validated['avatar'] ?? null) {
            if ($user->avatar_type === 'upload' && $user->avatar_value) {
                $this->deleteStoredUploadAvatar($user->avatar_value);
            }

            $upload = $validated['avatar'];
            $extension = $upload->extension() ?: $upload->getClientOriginalExtension();
            $newOriginalFileName = uniqid('', true).'.'.$extension;
            $fullPathToOriginal = Storage::disk('public')->putFileAs(
                config('avatar.original_path'),
                $upload,
                $newOriginalFileName
            );

            ProcessUploadImageAvatar::dispatchSync($fullPathToOriginal, $newOriginalFileName);

            $avatarType = 'upload';
            $avatarValue = $newOriginalFileName;
        } elseif ($applyPresetAvatar) {
            if ($user->avatar_type === 'upload' && $user->avatar_value) {
                $this->deleteStoredUploadAvatar($user->avatar_value);
            }

            $avatarType = 'default';
            $avatarValue = DefaultAvatar::from($validated['default_avatar'])->value;
        }

        $user->update([
            'username' => $validated['username'],
            'email' => $validated['email'],
            'avatar_type' => $avatarType,
            'avatar_value' => $avatarValue,
        ]);

        if (blank($validated['riot_tag'])) {
            $user->riotProfile?->delete();
        } else {
            $user->riotProfile()->updateOrCreate(
                ['user_id' => $user->id],
                ['riot_tag' => $validated['riot_tag']]

            );
        }
    }

    private function deleteStoredUploadAvatar(string $filename): void
    {
        $disk = Storage::disk('public');
        $disk->delete(config('avatar.original_path').'/'.$filename);

        foreach (config('avatar.sizes', []) as $size) {
            $directory = sprintf(config('avatar.variant_pattern'), $size['width'], $size['height']);
            $disk->delete($directory.'/'.$filename);
        }
    }
}