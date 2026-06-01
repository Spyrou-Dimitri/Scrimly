<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Enums\DefaultAvatar;
use App\Jobs\ProcessUploadImageAvatar;
use App\Models\User;
use App\Rules\ValidRiotId;
use App\Services\Riot\RiotApiClient;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, mixed>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'username' => $this->usernameRules(),
            'riot_tag' => $this->riotTagRules(),
            'password' => $this->passwordRules(),
            'avatar' => $this->avatarRules(),
            'default_avatar' => ['nullable', Rule::enum(DefaultAvatar::class)],
        ])->validate();

        $riotAccount = ValidRiotId::$validatedAccount;

        return DB::transaction(function () use ($input, $riotAccount) {
            [$avatarType, $avatarValue] = $this->resolveAvatar($input);

            $user = User::create([
                'username' => $input['username'],
                'email' => $input['email'],
                'password' => $input['password'],
                'avatar_type' => $avatarType,
                'avatar_value' => $avatarValue,
            ]);

            if (! empty($input['riot_tag'])) {
                $riotProfile = $user->riotProfile()->create([
                    'riot_tag' => $input['riot_tag'],
                    'riot_puuid' => $riotAccount['puuid'] ?? null,
                    'tier' => $riotAccount['soloQueue']['tier'] ?? null,
                    'rank' => $riotAccount['soloQueue']['rank'] ?? null,
                    'lp' => $riotAccount['soloQueue']['leaguePoints'] ?? null,
                    'wins' => $riotAccount['soloQueue']['wins'] ?? null,
                    'losses' => $riotAccount['soloQueue']['losses'] ?? null,
                    'synced_at' => now(),
                ]);

                $riotClient = new RiotApiClient;
                $recentMatches = $riotClient->getRecentMatches($riotAccount['puuid']);

                if ($recentMatches) {
                    foreach ($recentMatches as $match) {
                        $riotProfile->riotMatches()->create($match);
                    }
                }
            }

            return $user;
        });
    }

    protected function resolveAvatar(array $input): array
    {
        $fallbackDefault = $this->defaultAvatarFromInput($input);

        $upload = $input['avatar'] ?? null;
        if (! $upload instanceof UploadedFile || ! $upload->isValid()) {
            return ['default', $fallbackDefault->value];
        }

        $extension = $upload->extension() ?: $upload->getClientOriginalExtension();
        $filename = uniqid('', true).'.'.$extension;
        $storedPath = Storage::disk(config('avatar.disk'))->putFileAs(
            config('avatar.original_path'),
            $upload,
            $filename
        );

        if (! $storedPath) {
            return ['default', $fallbackDefault->value];
        }

        ProcessUploadImageAvatar::dispatchSync($storedPath, $filename);

        return ['upload', $filename];
    }

    protected function defaultAvatarFromInput(array $input): DefaultAvatar
    {
        $raw = $input['default_avatar'] ?? null;
        if ($raw === null || $raw === '') {
            return DefaultAvatar::cases()[0];
        }

        return DefaultAvatar::from($raw);
    }
}
