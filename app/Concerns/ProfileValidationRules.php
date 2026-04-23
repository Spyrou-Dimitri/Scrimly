<?php

namespace App\Concerns;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

trait ProfileValidationRules
{
    /**
     * Get the validation rules used to validate user profiles.
     *
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    protected function profileRules(?int $userId = null): array
    {
        return [
            'username' => $this->usernameRules(),
            'email' => $this->emailRules($userId),
        ];
    }

    /**
     * Get the validation rules used to validate user names.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function nameRules(): array
    {
        return ['required', 'string', 'max:255'];
    }

    /**
     * Get the validation rules used to validate user emails.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function emailRules(?int $userId = null): array
    {
        return [
            'required',
            'string',
            'email',
            'max:255',
            $userId === null
                ? Rule::unique(User::class)
                : Rule::unique(User::class)->ignore($userId),
        ];
    }

    protected function usernameRules(): array
    {
        return ['required', 'string', 'max:20', 'min:3', 'unique:users'];
    }

    /**
     * Get the validation rules used to validate the user's Riot tag.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function riotTagRules(?int $userId = null): array
    {
        return [
            'nullable',
            'string',
            'max:50',
            'regex:/^.{3,16}#[A-Za-z0-9]{2,5}$/',
            $userId === null
                ? Rule::unique(User::class, 'riot_tag')
                : Rule::unique(User::class, 'riot_tag')->ignore($userId),
        ];
    }
}
