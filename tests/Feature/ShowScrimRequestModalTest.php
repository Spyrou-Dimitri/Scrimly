<?php

use App\Enums\Language;
use App\Enums\LolGoal;
use App\Enums\LolServeur;
use App\Enums\RoleInGame;
use App\Enums\RoleInTeam;
use App\Enums\StatusInTeam;
use App\Enums\StatusScrimRequest;
use App\Models\ScrimRequest;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\Support\TeamPlayer;

test('la modal show-scrim-request s’affiche pour un membre de l’équipe destinataire', function (): void {
    $receiver = TeamPlayer::create();
    $requester = TeamPlayer::create();

    $scrimRequest = ScrimRequest::create([
        'status' => StatusScrimRequest::PENDING,
        'scheduled_date' => now()->addDay()->toDateString(),
        'scheduled_time' => '21:00:00',
        'number_of_games' => 2,
        'message' => null,
        'requester_team_id' => $requester->team->id,
        'receiver_team_id' => $receiver->team->id,
    ]);

    Livewire::actingAs($receiver->user)
        ->test('scrims.show-scrim-request', ['model_id' => $scrimRequest->id])
        ->assertSuccessful()
        ->assertSee($requester->team->name)
        ->assertSee(__('modals/scrims/show-scrim-request.accept'))
        ->assertSee(__('modals/scrims/show-scrim-request.refuse'));
});

test('la modal show-scrim-request renvoie une erreur 403 pour une équipe non concernée', function (): void {
    $creator = User::factory()->create();
    $receiverTeam = Team::create([
        'name' => Str::random(10),
        'slug' => Str::random(10),
        'tag' => Str::upper(Str::random(4)),
        'language' => Language::FR,
        'server' => LolServeur::EUW,
        'goal' => LolGoal::FUN,
        'creator_id' => $creator->id,
    ]);
    $requesterTeam = Team::create([
        'name' => Str::random(10),
        'slug' => Str::random(10),
        'tag' => Str::upper(Str::random(4)),
        'language' => Language::FR,
        'server' => LolServeur::EUW,
        'goal' => LolGoal::FUN,
        'creator_id' => $creator->id,
    ]);
    $otherTeam = Team::create([
        'name' => Str::random(10),
        'slug' => Str::random(10),
        'tag' => Str::upper(Str::random(4)),
        'language' => Language::FR,
        'server' => LolServeur::EUW,
        'goal' => LolGoal::FUN,
        'creator_id' => $creator->id,
    ]);
    $intruder = User::factory()->create(['current_team_id' => $otherTeam->id]);
    TeamMember::create([
        'team_id' => $otherTeam->id,
        'user_id' => $intruder->id,
        'roleInTeam' => RoleInTeam::PLAYER,
        'roleInGame' => RoleInGame::MID,
        'is_starter' => true,
        'status' => StatusInTeam::ACCEPTED,
        'joined_at' => now(),
    ]);

    $scrimRequest = ScrimRequest::create([
        'status' => StatusScrimRequest::PENDING,
        'scheduled_date' => now()->addDay()->toDateString(),
        'scheduled_time' => '21:00:00',
        'number_of_games' => 2,
        'message' => null,
        'requester_team_id' => $requesterTeam->id,
        'receiver_team_id' => $receiverTeam->id,
    ]);

    Livewire::actingAs($intruder)
        ->test('scrims.show-scrim-request', ['model_id' => $scrimRequest->id])
        ->assertForbidden();
});
