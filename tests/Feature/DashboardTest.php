<?php

use Tests\Support\TeamPlayer;

test('Utilisateurs non authentifiés sont redirigés vers la page de connexion', function () {
    $player = TeamPlayer::create();

    $response = $this->get(route('dashboard', ['slug' => $player->team->slug]));

    $response->assertRedirect(route('login'));
});

test('Utilisateurs authentifiés peuvent visiter le tableau de bord', function () {
    $player = TeamPlayer::create();

    $response = $this->actingAs($player->user)
        ->get(route('dashboard', ['slug' => $player->team->slug]));

    $response->assertOk();
});
