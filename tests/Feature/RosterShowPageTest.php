<?php

use App\Enums\Language;
use App\Enums\LolGoal;
use App\Enums\LolServeur;
use App\Enums\RoleInGame;
use App\Enums\RoleInTeam;
use App\Enums\StatusInTeam;
use App\Models\RiotMatch;
use App\Models\RiotProfile;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Livewire\Livewire;

beforeEach(function (): void {
    App::setLocale('fr');
});

test('la page profil roster affiche le membre et les libellés', function (): void {
    $creator = User::factory()->create();
    $team = Team::create([
        'name' => 'Équipe test',
        'slug' => 'equipe-test-roster-show-'.Str::random(8),
        'tag' => 'TST',
        'language' => Language::FR,
        'server' => LolServeur::EUW,
        'goal' => LolGoal::FUN,
        'creator_id' => $creator->id,
    ]);

    $player = User::factory()->create([
        'username' => 'joueur_roster_show',
        'current_team_id' => $team->id,
    ]);

    $teamMember = TeamMember::create([
        'team_id' => $team->id,
        'user_id' => $player->id,
        'roleInTeam' => RoleInTeam::PLAYER,
        'roleInGame' => RoleInGame::MID,
        'is_starter' => true,
        'status' => StatusInTeam::ACCEPTED,
        'joined_at' => now(),
    ]);

    Livewire::actingAs($player)
        ->test('pages::roster.show', ['slug' => $team->slug, 'id' => $teamMember->id])
        ->assertSuccessful()
        ->assertSee('joueur_roster_show', escape: false)
        ->assertSee(__('pages/roster/show.role_in_game_label'), escape: false)
        ->assertSee(__('pages/roster/show.tabs.matches'), escape: false);
});

test('l’onglet historique des parties affiche une partie synchronisée', function (): void {
    $creator = User::factory()->create();
    $team = Team::create([
        'name' => 'Équipe match history',
        'slug' => 'equipe-match-history-'.Str::random(8),
        'tag' => 'TMH',
        'language' => Language::FR,
        'server' => LolServeur::EUW,
        'goal' => LolGoal::FUN,
        'creator_id' => $creator->id,
    ]);

    $player = User::factory()->create([
        'username' => 'joueur_match_history',
        'current_team_id' => $team->id,
    ]);

    $teamMember = TeamMember::create([
        'team_id' => $team->id,
        'user_id' => $player->id,
        'roleInTeam' => RoleInTeam::PLAYER,
        'roleInGame' => RoleInGame::MID,
        'is_starter' => true,
        'status' => StatusInTeam::ACCEPTED,
        'joined_at' => now(),
    ]);

    $riotProfile = RiotProfile::factory()->for($player)->create();

    RiotMatch::query()->create([
        'riot_profile_id' => $riotProfile->id,
        'match_id' => 'EUW1_TEST_MATCH_'.Str::random(6),
        'game_duration' => 1725,
        'played_at' => now()->subHours(2),
        'champion_name' => 'Camille',
        'champion_id' => 164,
        'champion_level' => 14,
        'role' => 'TOP',
        'win' => true,
        'kills' => 12,
        'deaths' => 2,
        'assists' => 8,
        'cs' => 248,
        'items' => [3031, 3074, 0, 0, 0, 0, 3340],
    ]);

    Livewire::actingAs($player)
        ->test('pages::roster.show', ['slug' => $team->slug, 'id' => $teamMember->id])
        ->assertSuccessful()
        ->assertSee('Camille', escape: false)
        ->assertSee('12 / 2 / 8', escape: false)
        ->assertSee('28:45', escape: false)
        ->assertSee(__('pages/roster/show.matches.win'), escape: false)
        ->assertSee(__('pages/roster/show.matches.section_title'), escape: false);
});

test('la page profil roster répond 404 pour un membre d\'une autre équipe', function (): void {
    $creator = User::factory()->create();
    $team = Team::create([
        'name' => 'Équipe A',
        'slug' => 'equipe-a-roster-'.Str::random(8),
        'tag' => 'AAA',
        'language' => Language::FR,
        'server' => LolServeur::EUW,
        'goal' => LolGoal::FUN,
        'creator_id' => $creator->id,
    ]);

    $otherCreator = User::factory()->create();
    $otherTeam = Team::create([
        'name' => 'Équipe B',
        'slug' => 'equipe-b-roster-'.Str::random(8),
        'tag' => 'BBB',
        'language' => Language::FR,
        'server' => LolServeur::EUW,
        'goal' => LolGoal::FUN,
        'creator_id' => $otherCreator->id,
    ]);

    $player = User::factory()->create([
        'current_team_id' => $team->id,
    ]);

    TeamMember::create([
        'team_id' => $team->id,
        'user_id' => $player->id,
        'roleInTeam' => RoleInTeam::PLAYER,
        'roleInGame' => RoleInGame::MID,
        'is_starter' => true,
        'status' => StatusInTeam::ACCEPTED,
        'joined_at' => now(),
    ]);

    $foreignMember = TeamMember::create([
        'team_id' => $otherTeam->id,
        'user_id' => User::factory()->create()->id,
        'roleInTeam' => RoleInTeam::PLAYER,
        'roleInGame' => RoleInGame::TOP,
        'is_starter' => true,
        'status' => StatusInTeam::ACCEPTED,
        'joined_at' => now(),
    ]);

    expect(fn () => Livewire::actingAs($player)->test('pages::roster.show', [
        'slug' => $team->slug,
        'id' => $foreignMember->id,
    ]))->toThrow(ModelNotFoundException::class);
});
