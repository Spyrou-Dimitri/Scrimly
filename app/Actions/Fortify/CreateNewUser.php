<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use App\Rules\ValidRiotId;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'username' => $this->usernameRules(),
            'riot_tag' => $this->riotTagRules(),
            'password' => $this->passwordRules(),

        ])->validate();

        $riotAccount = ValidRiotId::$validatedAccount;

        return User::create([
            'username' => $input['username'],
            'email' => $input['email'],
            'password' => $input['password'],
            'riot_tag' => $input['riot_tag'],
            'riot_puuid' => $riotAccount['puuid'] ?? null,
            'tier' => $riotAccount['soloQueue']['tier'] ?? null,
            'rank' => $riotAccount['soloQueue']['rank'] ?? null,
            'lp' => $riotAccount['soloQueue']['leaguePoints'] ?? null,
        ]);
    }
}
