<?php

namespace App\Livewire\Forms;

use App\Models\ScrimGame;
use Livewire\Attributes\Validate;
use Livewire\Form;

class CreateScrimGame extends Form
{
    #[Validate]
    public string $title = '';

    #[Validate]
    public ?int $duration_minutes = null;

    #[Validate]
    public ?int $duration_seconds = null;

    #[Validate]
    public ?bool $is_victory = null;

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:100', 'min:3'],
            'duration_minutes' => ['required', 'integer', 'min:0'],
            'duration_seconds' => ['required', 'integer', 'min:0', 'max:59'],
            'is_victory' => ['required', 'boolean'],
        ];
    }

    public function store(int $scrimId): void
    {
        $validated = $this->validate();
        $scrimGame = ScrimGame::create([
            'title' => $validated['title'],
            'duration' => $validated['duration_minutes'] * 60 + $validated['duration_seconds'],
            'is_victory' => $validated['is_victory'],
            'scrim_id' => $scrimId,
        ]);
    }
}
