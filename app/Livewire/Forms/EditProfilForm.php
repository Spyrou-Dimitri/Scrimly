<?php

namespace App\Livewire\Forms;

use App\Enums\DefaultAvatar;
use App\Jobs\ProcessUploadImageAvatar;
use App\Models\User;
use App\Rules\ValidRiotId;
use App\Services\Riot\RiotApiClient;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
            'riot_tag' => [
                'bail',
                'nullable',
                'min:7',
                'max:22',
                'regex:/^[\p{L}\p{N}][\p{L}\p{N} ]{1,14}[\p{L}\p{N}]#[A-Za-z0-9]{3,5}$/u',
                Rule::when($this->riotTagChanged(), [new ValidRiotId]),
            ],
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

        DB::transaction(function () use ($validated, $user, $applyPresetAvatar): void {
            $avatarType = $user->avatar_type;
            $avatarValue = $user->avatar_value;

            if ($validated['avatar'] ?? null) {
                if ($user->avatar_type === 'upload' && $user->avatar_value) {
                    $this->deleteStoredUploadAvatar($user->avatar_value);
                }

                $upload = $validated['avatar'];
                $extension = $upload->extension() ?: $upload->getClientOriginalExtension();
                $newOriginalFileName = uniqid('', true).'.'.$extension;
                $fullPathToOriginal = $upload->storeAs(
                    config('avatar.original_path'),
                    $newOriginalFileName,
                    ['disk' => config('avatar.disk')]
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
            } elseif ($this->riotTagChanged()) {
                $riotAccount = ValidRiotId::$validatedAccount;
                $riotProfile = $user->riotProfile()->updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'riot_tag' => $validated['riot_tag'],
                        'riot_puuid' => $riotAccount['puuid'] ?? null,
                        'tier' => $riotAccount['soloQueue']['tier'] ?? null,
                        'rank' => $riotAccount['soloQueue']['rank'] ?? null,
                        'wins' => $riotAccount['soloQueue']['wins'] ?? null,
                        'losses' => $riotAccount['soloQueue']['losses'] ?? null,
                        'lp' => $riotAccount['soloQueue']['leaguePoints'] ?? null,
                        'synced_at' => now(),
                    ]
                );

                $riotProfile->riotMatches()->delete();
                $riotClient = new RiotApiClient;
                $recentMatches = $riotClient->getRecentMatches($riotAccount['puuid']);

                if ($recentMatches !== []) {
                    $riotProfile->riotMatches()->createMany($recentMatches);
                }
            }
        });
    }

    private function deleteStoredUploadAvatar(string $filename): void
    {
        $disk = Storage::disk(config('avatar.disk'));
        $disk->delete(config('avatar.original_path').'/'.$filename);

        foreach (config('avatar.sizes', []) as $size) {
            $directory = sprintf(config('avatar.variant_pattern'), $size['width'], $size['height']);
            $disk->delete($directory.'/'.$filename);
        }
    }

    private function riotTagChanged(): bool
    {
        $user = User::query()->with('riotProfile')->findOrFail(Auth::id());
        $futurNewRiotTag = filled($this->riot_tag) ? trim($this->riot_tag) : null;
        $oldRiotTag = $user->riotProfile?->riot_tag;

        return $futurNewRiotTag !== $oldRiotTag;
    }
}
