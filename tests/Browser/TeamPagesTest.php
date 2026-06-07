<?php

use Tests\Support\TeamPlayer;

test('la page de gestion d equipe est accessible', function () {
    $player = TeamPlayer::create();

    $this->actingAs($player->user);

    visit(route('team.index'))
        ->assertSee('Content de te revoir');
});

test('la page de creation d equipe est accessible', function () {
    $player = TeamPlayer::create();

    $this->actingAs($player->user);

    visit(route('team.create'))
        ->assertSee('Créer');
});

test('la page pour rejoindre une equipe est accessible', function () {
    $player = TeamPlayer::create();

    $this->actingAs($player->user);

    visit(route('team.join'))
        ->assertSee('Rejoindre une équipe');
});

test('la page de profil est accessible', function () {
    $player = TeamPlayer::create();

    $this->actingAs($player->user);

    visit(route('profile.show'))
        ->assertSee('Paramètres du compte');
});
