<?php

use App\Models\RiotMatch;
use App\Models\RiotProfile;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Tests\Support\TeamPlayer;

beforeEach(function (): void {
    App::setLocale('fr');
});

test('l onglet historique des parties est affiche par defaut', function () {
    $player = TeamPlayer::create();

    $this->actingAs($player->user);

    visit(route('roster.show', ['slug' => $player->team->slug, 'id' => $player->member->id]))
        ->assertSee('Historique des parties')
        ->assertSee('Aucun profil Riot lié à ce compte.');
});

test('cliquer sur devoirs en cours affiche le contenu des devoirs', function () {
    $player = TeamPlayer::create();

    $this->actingAs($player->user);

    visit(route('roster.show', ['slug' => $player->team->slug, 'id' => $player->member->id]))
        ->click('a[href*="tab=homework"]')
        ->assertSee('Aucun devoir en cours.')
        ->assertDontSee('Créneaux habituels');
});

test('cliquer sur disponibilites affiche les creneaux habituels', function () {
    $player = TeamPlayer::create();

    $this->actingAs($player->user);

    visit(route('roster.show', ['slug' => $player->team->slug, 'id' => $player->member->id]))
        ->click('a[href*="tab=availability"]')
        ->assertSee('Créneaux habituels')
        ->assertDontSee('Aucun devoir en cours.');
});

test('revenir sur historique des parties affiche le bon contenu', function () {
    $player = TeamPlayer::create();

    $this->actingAs($player->user);

    visit(route('roster.show', ['slug' => $player->team->slug, 'id' => $player->member->id]))
        ->click('a[href*="tab=availability"]')
        ->assertSee('Créneaux habituels')
        ->click('a[href*="tab=matches"]')
        ->assertSee('Aucun profil Riot lié à ce compte.')
        ->assertDontSee('Créneaux habituels');
});

test('l onglet historique des parties affiche une partie synchronisee', function () {
    $player = TeamPlayer::create();

    $riotProfile = RiotProfile::factory()->create([
        'user_id' => $player->user->id,
    ]);

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

    $this->actingAs($player->user);

    visit(route('roster.show', ['slug' => $player->team->slug, 'id' => $player->member->id]))
        ->click('a[href*="tab=homework"]')
        ->assertSee('Aucun devoir en cours.')
        ->click('a[href*="tab=matches"]')
        ->assertSee('Camille')
        ->assertSee('12 / 2 / 8')
        ->assertSee('Victoire');
});
