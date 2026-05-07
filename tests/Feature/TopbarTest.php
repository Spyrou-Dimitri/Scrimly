<?php

use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('switchTeam persists current_team_id and redirects', function () {
    $user = User::factory()->create();
    $teamA = Team::factory()->create(['creator_id' => $user->id]);
    $teamB = Team::factory()->create(['creator_id' => $user->id]);

    $user->teams()->attach($teamA->id, [
        'roleInTeam' => 'player',
        'roleInGame' => 'mid',
        'status' => 'accepted',
        'joined_at' => now(),
    ]);
    $user->teams()->attach($teamB->id, [
        'roleInTeam' => 'player',
        'roleInGame' => 'top',
        'status' => 'accepted',
        'joined_at' => now(),
    ]);

    $user->update(['current_team_id' => $teamA->id]);

    Livewire::actingAs($user)
        ->test('layout.topbar')
        ->call('switchTeam', $teamB->id)
        ->assertRedirect(route('roster.index', ['slug' => $teamB->slug]));

    expect($user->fresh()->current_team_id)->toBe($teamB->id);
});

test('switchTeam aborts when user does not belong to the team', function () {
    $user = User::factory()->create();
    $teamA = Team::factory()->create(['creator_id' => $user->id]);
    $otherTeam = Team::factory()->create();

    $user->teams()->attach($teamA->id, [
        'roleInTeam' => 'player',
        'roleInGame' => 'mid',
        'status' => 'accepted',
        'joined_at' => now(),
    ]);

    $user->update(['current_team_id' => $teamA->id]);

    Livewire::actingAs($user)
        ->test('layout.topbar')
        ->call('switchTeam', $otherTeam->id)
        ->assertForbidden();
});

test('loadTeams returns other teams excluding current team', function () {
    $user = User::factory()->create();
    $teamA = Team::factory()->create(['creator_id' => $user->id]);
    $teamB = Team::factory()->create(['creator_id' => $user->id]);

    $user->teams()->attach($teamA->id, [
        'roleInTeam' => 'player',
        'roleInGame' => 'mid',
        'status' => 'accepted',
        'joined_at' => now(),
    ]);
    $user->teams()->attach($teamB->id, [
        'roleInTeam' => 'player',
        'roleInGame' => 'top',
        'status' => 'accepted',
        'joined_at' => now(),
    ]);

    $user->update(['current_team_id' => $teamA->id]);

    $component = Livewire::actingAs($user)
        ->test('layout.topbar')
        ->call('loadTeams');

    $userTeams = $component->get('userTeams');

    expect($userTeams)->toHaveCount(1)
        ->and($userTeams->first()->id)->toBe($teamB->id);
});

test('unloadTeams clears the teams collection', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['creator_id' => $user->id]);

    $user->teams()->attach($team->id, [
        'roleInTeam' => 'player',
        'roleInGame' => 'mid',
        'status' => 'accepted',
        'joined_at' => now(),
    ]);

    $user->update(['current_team_id' => $team->id]);

    $component = Livewire::actingAs($user)
        ->test('layout.topbar')
        ->call('loadTeams')
        ->call('unloadTeams');

    expect($component->get('userTeams'))->toHaveCount(0);
});
