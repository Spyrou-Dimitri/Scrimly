<?php

namespace App\Concerns;

use App\Models\User;
use App\Rules\ValidRiotId;
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

    public function avatarRules(): array
    {
        return ['nullable', 'image', 'max:2048'];
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
    protected function riotTagRules(?int $userId = null, bool $required = true): array
    {
        return [
            'bail',
            'nullable',
            'string',
            'min:7',
            'max:22',
            'regex:/^[\p{L}\p{N}][\p{L}\p{N} ]{1,14}[\p{L}\p{N}]#[A-Za-z0-9]{3,5}$/u',
            new ValidRiotId,
        ];
    }
}
