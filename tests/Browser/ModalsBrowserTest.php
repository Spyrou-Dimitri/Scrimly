<?php

use App\Enums\RoleInGame;
use App\Enums\RoleInTeam;
use App\Enums\StatusInTeam;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Support\Facades\App;
use Tests\Support\TeamPlayer;

beforeEach(function (): void {
    App::setLocale('fr');
});

test('la modal creneaux habituels s affiche', function () {
    $player = TeamPlayer::create();

    $this->actingAs($player->user);

    visit(route('roster.show', ['slug' => $player->team->slug, 'id' => $player->member->id]).'?tab=availability')
        ->click('Modifier')
        ->assertSee('Créneaux habituels')
        ->assertSee('Enregistrer');
});

test('la modal ajouter une absence s affiche', function () {
    $player = TeamPlayer::create();

    $this->actingAs($player->user);

    visit(route('roster.show', ['slug' => $player->team->slug, 'id' => $player->member->id]).'?tab=availability')
        ->click('Ajouter')
        ->assertSee('Ajouter une absence')
        ->assertSee('Raison');
});

test('la modal changer de mot de passe s affiche', function () {
    $player = TeamPlayer::create();

    $this->actingAs($player->user);

    visit(route('profile.show'))
        ->click('Changer de mot de passe')
        ->assertSee('Ancien mot de passe')
        ->assertSee('Nouveau mot de passe');
});
test('la modal envoyer en remplacants s affiche pour un coach', function () {
    $coach = TeamPlayer::create();
    $coach->member->update([
        'roleInTeam' => RoleInTeam::COACH,
        'roleInGame' => null,
        'is_starter' => false,
    ]);

    $starterUser = User::factory()->create([
        'username' => 'titulaire_test',
        'current_team_id' => $coach->team->id,
    ]);

    TeamMember::create([
        'team_id' => $coach->team->id,
        'user_id' => $starterUser->id,
        'roleInTeam' => RoleInTeam::PLAYER,
        'roleInGame' => RoleInGame::MID,
        'is_starter' => true,
        'status' => StatusInTeam::ACCEPTED,
        'joined_at' => now(),
    ]);

    $this->actingAs($coach->user);

    visit(route('roster.index', ['slug' => $coach->team->slug]))
        ->click('article:has-text("titulaire_test") [aria-label="Actions du joueur"]')
        ->click('Retirer du titulaire')
        ->assertSee('Passer remplaçant');
});
