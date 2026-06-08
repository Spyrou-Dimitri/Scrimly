<?php

namespace App\Livewire\Forms\Scrim;

use App\Enums\TypeScrimGameNote;
use App\Livewire\Forms\Scrim\Concerns\SanitizesScrimGameScores;
use App\Models\ScrimGame;
use App\Models\ScrimGamePlayer;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Form;

class EditScrimGameForm extends Form
{
    use SanitizesScrimGameScores;

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
            'players.*.kills' => ['nullable', 'numeric', 'min:0'],
            'players.*.deaths' => ['nullable', 'numeric', 'min:0'],
            'players.*.assists' => ['nullable', 'numeric', 'min:0'],
            'opponentTeamMembersStarters' => ['required', 'array', 'min:5', 'max:5'],
            'opponentTeamMembersStarters.*.champion' => ['required', 'string', Rule::in(collect(getChampionsList())->pluck('name'))],
            'opponentTeamMembersStarters.*.kills' => ['nullable', 'numeric', 'min:0'],
            'opponentTeamMembersStarters.*.deaths' => ['nullable', 'numeric', 'min:0'],
            'opponentTeamMembersStarters.*.assists' => ['nullable', 'numeric', 'min:0'],
            'scrimGameNotes' => ['array'],
            'scrimGameNotes.*.type' => ['required', Rule::enum(TypeScrimGameNote::class)],
            'scrimGameNotes.*.note' => ['required', 'string', 'min:3', 'max:500'],
        ];
    }

    public function update(int $scrimGameId): bool
    {
        $validated = $this->validate();
        if (Gate::denies('manageTeam', User::class)) {
            return false;
        }

        DB::transaction(function () use ($scrimGameId, $validated) {
            $scrimGame = ScrimGame::findOrFail($scrimGameId);
            $scrimGame->update([
                'title' => $validated['title'],
                'duration' => $validated['duration_minutes'] * 60 + $validated['duration_seconds'],
                'is_victory' => $validated['is_victory'],
                'opponent_team_members_starters' => $validated['opponentTeamMembersStarters'],
            ]);

            foreach ($validated['players'] as $teamMemberId => $player) {
                ScrimGamePlayer::query()
                    ->where('scrim_game_id', $scrimGameId)
                    ->where('team_member_id', $teamMemberId)
                    ->update([
                        'champion' => $player['champion'],
                        'kills' => (int) ($player['kills'] ?? 0),
                        'deaths' => (int) ($player['deaths'] ?? 0),
                        'assists' => (int) ($player['assists'] ?? 0),
                    ]);
            }

            $scrimGame->scrimGameNotes()->delete();

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
