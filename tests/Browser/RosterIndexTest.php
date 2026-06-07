<?php

use App\Enums\RoleInGame;
use App\Enums\RoleInTeam;
use App\Enums\StatusInTeam;
use App\Models\TeamMember;
use App\Models\User;
use Tests\Support\TeamPlayer;

test('la page d index des rosters est accessible', function () {
    $player = TeamPlayer::create();

    $this->actingAs($player->user);

    visit(route('roster.index', ['slug' => $player->team->slug]))
        ->assertSee('Gestion du roster');
});

test('le popup de gestion d un joueur est accessible uniquement pour les coachs', function () {
    $coach = TeamPlayer::create();
    $coach->member->update([
        'roleInTeam' => RoleInTeam::COACH,
        'roleInGame' => null,
        'is_starter' => false,
    ]);

    $playerUser = User::factory()->create([
        'username' => 'joueur_test',
        'current_team_id' => $coach->team->id,
    ]);

    TeamMember::create([
        'team_id' => $coach->team->id,
        'user_id' => $playerUser->id,
        'roleInTeam' => RoleInTeam::PLAYER,
        'roleInGame' => RoleInGame::MID,
        'is_starter' => true,
        'status' => StatusInTeam::ACCEPTED,
        'joined_at' => now(),
    ]);

    $this->actingAs($coach->user);

    visit(route('roster.index', ['slug' => $coach->team->slug]))
        ->click('article:has-text("joueur_test") [aria-label="Actions du joueur"]')
        ->assertSee('Retirer du titulaire');
});

test('la page d invitation est accessible pour un coach', function () {
    $coach = TeamPlayer::create();
    $coach->member->update([
        'roleInTeam' => RoleInTeam::COACH,
        'roleInGame' => null,
        'is_starter' => false,
    ]);

    $this->actingAs($coach->user);

    visit(route('roster.invitations.create', ['slug' => $coach->team->slug]))
        ->assertSee('Inviter un joueur');
});
