<?php

use Tests\Support\TeamPlayer;

test('le layout équipe affiche les raccourcis vers le profil membre et les paramètres du compte', function () {
    $player = TeamPlayer::create();

    $rosterUrl = route('roster.show', [
        'slug' => $player->team->slug,
        'id' => $player->member->id,
    ]);

    $response = $this->actingAs($player->user)
        ->get(route('dashboard', ['slug' => $player->team->slug]));

    $response->assertOk();
    $response->assertSee('data-test="sidebar-member-profile-link"', false);
    $response->assertSee('data-test="topbar-team-member-profile-link"', false);
    $response->assertSee('data-test="topbar-account-settings-link"', false);
    $response->assertSee($rosterUrl, false);
    $response->assertSee(__('layouts/team.view_member_profile_title'));
    $response->assertSee(__('layouts/team.account_settings_title'));
    $response->assertSee($player->user->username);
});
