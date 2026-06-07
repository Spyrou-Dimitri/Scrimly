<?php

use Tests\Support\TeamPlayer;

test('la page calendrier est accessible', function () {
    $player = TeamPlayer::create();

    $this->actingAs($player->user);

    visit(route('calendar.index', ['slug' => $player->team->slug]))
        ->assertSee('Calendrier');
});
