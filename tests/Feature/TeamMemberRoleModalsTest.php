<?php

use App\Enums\Language;
use App\Enums\LolGoal;
use App\Enums\LolServeur;
use App\Enums\RoleInGame;
use App\Enums\RoleInTeam;
use App\Enums\StatusInTeam;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Support\Str;
use Livewire\Livewire;

test('La modal de promotion titulaire affiche le conflit quand le poste est déjà occupé', function () {
    $creator = User::factory()->create();
    $team = Team::create([
        'name' => Str::random(10),
        'slug' => Str::random(10),
        'tag' => Str::upper(Str::random(4)),
        'language' => Language::FR,
        'server' => LolServeur::EUW,
        'goal' => LolGoal::FUN,
        'creator_id' => $creator->id,
    ]);

    $starterUser = User::factory()->create(['username' => 'starter_player']);
    $benchUser = User::factory()->create(['username' => 'bench_player']);

    TeamMember::create([
        'team_id' => $team->id,
        'user_id' => $starterUser->id,
        'roleInTeam' => RoleInTeam::PLAYER,
        'roleInGame' => RoleInGame::MID,
        'is_starter' => true,
        'status' => StatusInTeam::ACCEPTED,
        'joined_at' => now(),
    ]);

    $benchMember = TeamMember::create([
        'team_id' => $team->id,
        'user_id' => $benchUser->id,
        'roleInTeam' => RoleInTeam::PLAYER,
        'roleInGame' => RoleInGame::MID,
        'is_starter' => false,
        'status' => StatusInTeam::ACCEPTED,
        'joined_at' => now(),
    ]);

    Livewire::test('modals::promote-to-starter', ['model_id' => $benchMember->id])
        ->assertSee('starter_player')
        ->assertSee('Mid')
        ->assertSee('bench_player');
});

test('Promouvoir un remplaçant rétrograde automatiquement le titulaire actuel sur le même rôle', function () {
    $creator = User::factory()->create();
    $team = Team::create([
        'name' => Str::random(10),
        'slug' => Str::random(10),
        'tag' => Str::upper(Str::random(4)),
        'language' => Language::FR,
        'server' => LolServeur::EUW,
        'goal' => LolGoal::FUN,
        'creator_id' => $creator->id,
    ]);

    $starterUser = User::factory()->create();
    $benchUser = User::factory()->create();

    $starterMember = TeamMember::create([
        'team_id' => $team->id,
        'user_id' => $starterUser->id,
        'roleInTeam' => RoleInTeam::PLAYER,
        'roleInGame' => RoleInGame::ADC,
        'is_starter' => true,
        'status' => StatusInTeam::ACCEPTED,
        'joined_at' => now(),
    ]);

    $benchMember = TeamMember::create([
        'team_id' => $team->id,
        'user_id' => $benchUser->id,
        'roleInTeam' => RoleInTeam::PLAYER,
        'roleInGame' => RoleInGame::ADC,
        'is_starter' => false,
        'status' => StatusInTeam::ACCEPTED,
        'joined_at' => now(),
    ]);

    Livewire::test('modals::promote-to-starter', ['model_id' => $benchMember->id])
        ->call('promoteToStarter');

    expect($benchMember->fresh()->is_starter)->toBeTrue();
    expect($starterMember->fresh()->is_starter)->toBeFalse();
});

test('envoyer sur le banc passe le statut titulaire à faux', function () {
    $creator = User::factory()->create();
    $team = Team::create([
        'name' => Str::random(10),
        'slug' => Str::random(10),
        'tag' => Str::upper(Str::random(4)),
        'language' => Language::FR,
        'server' => LolServeur::EUW,
        'goal' => LolGoal::FUN,
        'creator_id' => $creator->id,
    ]);

    $user = User::factory()->create();

    $starterMember = TeamMember::create([
        'team_id' => $team->id,
        'user_id' => $user->id,
        'roleInTeam' => RoleInTeam::PLAYER,
        'roleInGame' => RoleInGame::TOP,
        'is_starter' => true,
        'status' => StatusInTeam::ACCEPTED,
        'joined_at' => now(),
    ]);

    Livewire::test('modals::send-to-bench', ['model_id' => $starterMember->id])
        ->call('sendToBench');

    expect($starterMember->fresh()->is_starter)->toBeFalse();
});
