<?php

use App\Enums\RoleInGame;
use App\Enums\RoleInTeam;
use App\Enums\StatusInTeam;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;

beforeEach(function (): void {
    $this->team = Team::factory()->create();

    $this->user = User::factory()->create([
        'current_team_id' => $this->team->id,
    ]);

    $this->teamMember = TeamMember::create([
        'team_id' => $this->team->id,
        'user_id' => $this->user->id,
        'roleInTeam' => RoleInTeam::PLAYER,
        'roleInGame' => RoleInGame::MID,
        'is_starter' => true,
        'status' => StatusInTeam::ACCEPTED,
        'joined_at' => now(),
    ]);
});

test('Utilisateurs non authentifiés sont redirigés vers la page de connexion', function () {
    $response = $this->get(route('dashboard', ['slug' => $this->team->slug]));

    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $response = $this->actingAs($this->user)
        ->get(route('dashboard', ['slug' => $this->team->slug]));

    $response->assertOk();
});
