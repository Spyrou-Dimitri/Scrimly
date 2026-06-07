<?php

use App\Enums\RoleInGame;
use App\Enums\RoleInTeam;
use App\Enums\StatusInTeam;
use App\Enums\StatusScrim;
use App\Enums\StatusScrimRequest;
use App\Models\Scrim;
use App\Models\ScrimGamePlayer;
use App\Models\ScrimRequest;
use App\Models\TeamMember;
use App\Models\User;
use Livewire\Livewire;
use Tests\Support\TeamPlayer;

test('les scores avec zéros en tête sont convertis en entiers à la soumission', function (): void {
    $coach = TeamPlayer::create();
    $coach->member->update([
        'roleInTeam' => RoleInTeam::COACH,
        'roleInGame' => null,
        'is_starter' => false,
    ]);

    $starterIds = [];

    foreach ([RoleInGame::TOP, RoleInGame::JUNGLE, RoleInGame::MID, RoleInGame::ADC, RoleInGame::SUPPORT] as $role) {
        $starter = TeamMember::create([
            'team_id' => $coach->team->id,
            'user_id' => User::factory()->create()->id,
            'roleInTeam' => RoleInTeam::PLAYER,
            'roleInGame' => $role,
            'is_starter' => true,
            'status' => StatusInTeam::ACCEPTED,
            'joined_at' => now(),
        ]);

        $starterIds[] = $starter->id;
    }

    $opponent = TeamPlayer::create();

    $scrimRequest = ScrimRequest::create([
        'status' => StatusScrimRequest::ACCEPTED,
        'scheduled_date' => now()->addDay()->toDateString(),
        'scheduled_time' => '21:00:00',
        'number_of_games' => 1,
        'message' => null,
        'requester_team_id' => $opponent->team->id,
        'receiver_team_id' => $coach->team->id,
    ]);

    $scrim = Scrim::create([
        'scheduled_date' => $scrimRequest->scheduled_date,
        'scheduled_time' => $scrimRequest->scheduled_time,
        'number_of_games' => $scrimRequest->number_of_games,
        'status' => StatusScrim::SCHEDULED,
        'scrim_request_id' => $scrimRequest->id,
        'team_id' => $coach->team->id,
        'opponent_team_id' => $opponent->team->id,
    ]);

    $champion = collect(getChampionsList())->pluck('name')->first();
    $memberId = $starterIds[0];

    $component = Livewire::actingAs($coach->user)
        ->test('pages::scrims.games.create', ['id' => $scrim->id])
        ->set('form.title', 'Game test')
        ->set('form.duration_minutes', 25)
        ->set('form.duration_seconds', 30)
        ->set('form.is_victory', true);

    foreach ($starterIds as $id) {
        $component->set("form.players.{$id}.champion", $champion);
    }

    foreach (['top', 'jungle', 'mid', 'bot', 'support'] as $role) {
        $component->set("form.opponentTeamMembersStarters.{$role}.champion", $champion);
    }

    $component
        ->set("form.players.{$memberId}.kills", '014')
        ->set("form.players.{$memberId}.deaths", '003')
        ->set('form.opponentTeamMembersStarters.top.kills', '007')
        ->call('createGame')
        ->assertHasNoErrors();

    $player = ScrimGamePlayer::query()
        ->where('team_member_id', $memberId)
        ->first();

    expect($player->kills)->toBe(14)
        ->and($player->deaths)->toBe(3);
});
