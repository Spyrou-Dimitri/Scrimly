<?php

namespace App\Livewire\Forms\Scrim;

use App\Enums\TypeScrimGameNote;
use App\Models\ScrimGame;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Form;

class CreateScrimGameForm extends Form
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

    #[Validate]
    public array $opponentTeamMembersStarters = [];

    #[Validate]
    public array $scrimGameNotes = [];

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
            'opponentTeamMembersStarters' => ['required', 'array', 'min:5', 'max:5'],
            'opponentTeamMembersStarters.*.champion' => ['required', 'string', Rule::in(collect(getChampionsList())->pluck('name'))],
            'opponentTeamMembersStarters.*.kills' => ['nullable', 'integer', 'min:0'],
            'opponentTeamMembersStarters.*.deaths' => ['nullable', 'integer', 'min:0'],
            'opponentTeamMembersStarters.*.assists' => ['nullable', 'integer', 'min:0'],
            'scrimGameNotes' => ['array'],
            'scrimGameNotes.*.type' => ['required', Rule::enum(TypeScrimGameNote::class)],
            'scrimGameNotes.*.note' => ['required', 'string', 'min:3', 'max:500'],
        ];
    }

    public function store(int $scrimId): bool
    {
        $validated = $this->validate();

        if (Gate::denies('manageTeam', User::class)) {
            return false;
        }
        $opponentStarters = [
            'top' => $validated['opponentTeamMembersStarters']['top'],
            'jungle' => $validated['opponentTeamMembersStarters']['jungle'],
            'mid' => $validated['opponentTeamMembersStarters']['mid'],
            'bot' => $validated['opponentTeamMembersStarters']['bot'],
            'support' => $validated['opponentTeamMembersStarters']['support'],
        ];

        DB::transaction(function () use ($validated, $scrimId, $opponentStarters) {
            $scrimGame = ScrimGame::create([
                'title' => $validated['title'],
                'duration' => $validated['duration_minutes'] * 60 + $validated['duration_seconds'],
                'is_victory' => $validated['is_victory'],
                'scrim_id' => $scrimId,
                'opponent_team_members_starters' => $opponentStarters,
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

            foreach ($validated['scrimGameNotes'] as $note) {
                $scrimGame->scrimGameNotes()->create([
                    'type' => $note['type'],
                    'note' => $note['note'],
                ]);
            }
        });

        return true;
    }
}
