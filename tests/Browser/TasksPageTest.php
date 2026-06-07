<?php

use Tests\Support\TeamPlayer;

test('la page des devoirs est accessible pour un joueur', function () {
    $player = TeamPlayer::create();

    $this->actingAs($player->user);

    visit(route('tasks.index', ['slug' => $player->team->slug]))
        ->assertSee('Mes devoirs');
});
