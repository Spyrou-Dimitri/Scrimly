<?php

use Tests\Support\TeamPlayer;

test('la page de gestion des scrims est accessible', function () {
    $player = TeamPlayer::create();

    $this->actingAs($player->user);

    visit(route('scrims.index', ['slug' => $player->team->slug]))
        ->assertSee('Gestion des scrims');
});

test('la page de recherche de scrims est accessible', function () {
    $player = TeamPlayer::create();

    $this->actingAs($player->user);

    visit(route('scrims.find', ['slug' => $player->team->slug]))
        ->assertSee('Trouver une équipe');
});
