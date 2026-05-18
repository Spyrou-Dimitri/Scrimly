<?php

namespace App\Livewire\Forms;

use App\Models\ScrimGame;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
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

    #[Validate]
    public array $players = [];

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:100', 'min:3'],
            'duration_minutes' => ['required', 'integer', 'min:0'],
            'duration_seconds' => ['required', 'integer', 'min:0', 'max:59'],
            'is_victory' => ['required', 'boolean'],
            'players' => ['required', 'array', 'min:5', 'max:5'],
            'players.*.champion' => ['required', 'string', Rule::in(collect(getChampionsList())->pluck('name'))],
            'players.*.kills' => ['nullable', 'integer', 'min:0'],
            'players.*.deaths' => ['nullable', 'integer', 'min:0'],
            'players.*.assists' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function store(int $scrimId): void
    {
        $validated = $this->validate();
        DB::transaction(function () use ($validated, $scrimId) {
            $scrimGame = ScrimGame::create([
                'title' => $validated['title'],
                'duration' => $validated['duration_minutes'] * 60 + $validated['duration_seconds'],
                'is_victory' => $validated['is_victory'],
                'scrim_id' => $scrimId,
            ]);
            foreach ($validated['players'] as $teamMemberId => $player) {
                $scrimGame->scrimGamePlayers()->create([
                    'team_member_id' => $teamMemberId,
                    'champion' => $player['champion'],
                    'kills' => $player['kills'],
                    'deaths' => $player['deaths'],
                    'assists' => $player['assists'],
                ]);
            }
        });
    }
}
