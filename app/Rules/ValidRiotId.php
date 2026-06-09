<?php

namespace App\Rules;

use App\Services\Riot\RiotApiClient;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class ValidRiotId implements ValidationRule
{
    public static ?array $validatedAccount = null;

    private RiotApiClient $client;

    public function __construct()
    {
        $this->client = new RiotApiClient;
    }

    /**
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        static::$validatedAccount = null;

        if (! is_string($value) || ! str_contains($value, '#')) {
            return;
        }

        [$gameName, $tagLine] = explode('#', $value, 2);

        $account = $this->client->getAccount($gameName, $tagLine);

        if ($account === null) {
            $fail(__('profil/profil.riot_id_invalid'));

            return;
        }

        static::$validatedAccount = $account;
    }
}
