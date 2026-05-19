<?php

use App\Enums\Language;
use App\Enums\LolGoal;
use App\Enums\LolServeur;
use App\Enums\RoleInGame;
use App\Enums\RoleInTeam;
use App\Enums\StatusInTeam;
use App\Enums\StatusScrim;
use App\Enums\StatusScrimRequest;
use App\Models\Scrim;
use App\Models\ScrimGame;
use App\Models\ScrimRequest;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Support\Str;
use Livewire\Livewire;

test('la modal delete-game affiche le contenu pour un membre de l’équipe propriétaire du scrim', function (): void {
    [$team, $member, $scrimGame] = createScrimGameFixture();

    Livewire::actingAs($member)
        ->test('scrims.games.delete-game', ['model_id' => $scrimGame->id])
        ->assertSuccessful()
        ->assertSee(__('modals/scrims/games/delete-game.body_heading'))
        ->assertSee(__('modals/scrims/games/delete-game.body_legend'))
        ->assertSee($scrimGame->title);
});

test('la modal delete-game supprime la game et dispatch les événements', function (): void {
    [$team, $member, $scrimGame] = createScrimGameFixture();

    Livewire::actingAs($member)
        ->test('scrims.games.delete-game', ['model_id' => $scrimGame->id])
        ->call('deleteGame')
        ->assertDispatched('close_modal')
        ->assertDispatched('refresh_scrim')
        ->assertDispatched('toast');

    expect(ScrimGame::query()->find($scrimGame->id))->toBeNull();
});

test('la modal delete-game renvoie une erreur 403 pour une équipe non concernée', function (): void {
    [$team, , $scrimGame] = createScrimGameFixture();

    $otherTeam = Team::create([
        'name' => Str::random(10),
        'slug' => Str::random(10),
        'tag' => Str::upper(Str::random(4)),
        'language' => Language::FR,
        'server' => LolServeur::EUW,
        'goal' => LolGoal::FUN,
        'creator_id' => User::factory()->create()->id,
    ]);

    $outsider = User::factory()->create(['current_team_id' => $otherTeam->id]);

    TeamMember::create([
        'team_id' => $otherTeam->id,
        'user_id' => $outsider->id,
        'roleInTeam' => RoleInTeam::PLAYER,
        'roleInGame' => RoleInGame::MID,
        'is_starter' => true,
        'status' => StatusInTeam::ACCEPTED,
        'joined_at' => now(),
    ]);

    Livewire::actingAs($outsider)
        ->test('scrims.games.delete-game', ['model_id' => $scrimGame->id])
        ->assertForbidden();
});

/**
 * @return array{0: Team, 1: User, 2: ScrimGame}
 */
function createScrimGameFixture(): array
{
    $creator = User::factory()->create();
    $opponentCreator = User::factory()->create();

    $team = Team::create([
        'name' => Str::random(10),
        'slug' => Str::random(10),
        'tag' => Str::upper(Str::random(4)),
        'language' => Language::FR,
        'server' => LolServeur::EUW,
        'goal' => LolGoal::FUN,
        'creator_id' => $creator->id,
    ]);

    $opponentTeam = Team::create([
        'name' => Str::random(10),
        'slug' => Str::random(10),
        'tag' => Str::upper(Str::random(4)),
        'language' => Language::FR,
        'server' => LolServeur::EUW,
        'goal' => LolGoal::FUN,
        'creator_id' => $opponentCreator->id,
    ]);

    $member = User::factory()->create(['current_team_id' => $team->id]);

    TeamMember::create([
        'team_id' => $team->id,
        'user_id' => $member->id,
        'roleInTeam' => RoleInTeam::PLAYER,
        'roleInGame' => RoleInGame::MID,
        'is_starter' => true,
        'status' => StatusInTeam::ACCEPTED,
        'joined_at' => now(),
    ]);

    $scrimRequest = ScrimRequest::create([
        'status' => StatusScrimRequest::ACCEPTED,
        'scheduled_date' => now()->addDay()->toDateString(),
        'scheduled_time' => '21:00:00',
        'number_of_games' => 3,
        'message' => 'Message test scrim',
        'requester_team_id' => $team->id,
        'receiver_team_id' => $opponentTeam->id,
    ]);

    $scrim = Scrim::create([
        'scheduled_date' => now()->addDay()->toDateString(),
        'scheduled_time' => '21:00:00',
        'number_of_games' => 3,
        'status' => StatusScrim::SCHEDULED,
        'scrim_request_id' => $scrimRequest->id,
        'opponent_team_id' => $opponentTeam->id,
        'team_id' => $team->id,
    ]);

    $scrimGame = ScrimGame::create([
        'scrim_id' => $scrim->id,
        'title' => 'Game 1',
        'is_victory' => true,
        'duration' => 1800,
        'opponent_team_members_starters' => [],
    ]);

    return [$team, $member, $scrimGame];
}
