<?php

use Tests\Support\TeamPlayer;

test('le tableau de bord est accessible', function () {
    $player = TeamPlayer::create();

    $this->actingAs($player->user);

    visit(route('dashboard', ['slug' => $player->team->slug]))
        ->assertSee('Roster');
});
