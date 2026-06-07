<?php

use App\Enums\RoleInTeam;
use App\Enums\StatusInTeam;
use App\Models\Task;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Support\Facades\App;
use Livewire\Livewire;
use Tests\Support\TeamPlayer;

beforeEach(function (): void {
    App::setLocale('fr');
});

test('un joueur obtient 403 sur la page d\'édition d\'un devoir', function (): void {
    $player = TeamPlayer::create();

    $coach = User::factory()->create([
        'current_team_id' => $player->team->id,
    ]);

    $coachMember = TeamMember::create([
        'team_id' => $player->team->id,
        'user_id' => $coach->id,
        'roleInTeam' => RoleInTeam::COACH,
        'roleInGame' => null,
        'is_starter' => false,
        'status' => StatusInTeam::ACCEPTED,
        'joined_at' => now(),
    ]);

    $task = Task::create([
        'title' => 'Devoir édition',
        'description' => null,
        'deadline' => now()->addDay()->toDateString(),
        'created_by' => $coachMember->id,
        'team_member_id' => $player->member->id,
        'team_id' => $player->team->id,
    ]);

    Livewire::actingAs($player->user)
        ->test('pages::tasks.edit', ['id' => $task->id])
        ->assertForbidden();
});
