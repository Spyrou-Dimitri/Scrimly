<?php

use App\Enums\RoleInGame;
use App\Enums\RoleInTeam;
use App\Enums\StatusInTeam;
use App\Models\Subtask;
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

test('un coach peut modifier le devoir sans changer le joueur assigné', function (): void {
    $coach = TeamPlayer::create();
    $coach->member->update([
        'roleInTeam' => RoleInTeam::COACH,
        'roleInGame' => null,
        'is_starter' => false,
    ]);

    $assignee = User::factory()->create([
        'username' => 'assignee_edit_fixture',
        'current_team_id' => $coach->team->id,
    ]);

    $assigneeMembership = TeamMember::create([
        'team_id' => $coach->team->id,
        'user_id' => $assignee->id,
        'roleInTeam' => RoleInTeam::PLAYER,
        'roleInGame' => RoleInGame::MID,
        'is_starter' => true,
        'status' => StatusInTeam::ACCEPTED,
        'joined_at' => now(),
    ]);

    $otherPlayer = User::factory()->create([
        'current_team_id' => $coach->team->id,
    ]);

    $otherMembership = TeamMember::create([
        'team_id' => $coach->team->id,
        'user_id' => $otherPlayer->id,
        'roleInTeam' => RoleInTeam::PLAYER,
        'roleInGame' => RoleInGame::ADC,
        'is_starter' => true,
        'status' => StatusInTeam::ACCEPTED,
        'joined_at' => now(),
    ]);

    $task = Task::create([
        'title' => 'Titre avant',
        'description' => 'Ancienne',
        'deadline' => now()->addDays(3)->toDateString(),
        'created_by' => $coach->member->id,
        'team_member_id' => $assigneeMembership->id,
        'team_id' => $coach->team->id,
    ]);

    $sub = Subtask::create([
        'task_id' => $task->id,
        'title' => 'Sous-tâche une',
        'is_completed' => false,
    ]);

    $newDue = now()->addDays(5)->toDateString();

    Livewire::actingAs($coach->user)
        ->test('pages::tasks.edit', ['id' => $task->id])
        ->assertSeeText(__('pages/tasks/edit.main_legend'))
        ->assertSeeText('assignee_edit_fixture')
        ->set('form.title', 'Titre après mise à jour')
        ->set('form.description', 'Nouvelle description')
        ->set('form.dueDate', $newDue)
        ->set('form.subtasks', [
            [
                'id' => $sub->id,
                'title' => 'Sous-tâche une modifiée',
                'is_completed' => true,
            ],
        ])
        ->call('update')
        ->assertHasNoErrors()
        ->assertRedirect(route('tasks.index', ['slug' => $coach->team->slug]));

    $task->refresh();

    expect($task->title)->toBe('Titre après mise à jour')
        ->and($task->description)->toBe('Nouvelle description')
        ->and($task->deadline->toDateString())->toBe($newDue)
        ->and($task->team_member_id)->toBe($assigneeMembership->id);

    $sub->refresh();

    expect($sub->title)->toBe('Sous-tâche une modifiée')
        ->and($sub->is_completed)->toBeTrue();

    Livewire::actingAs($coach->user)
        ->test('pages::tasks.edit', ['id' => $task->id])
        ->set('form.assigneeUserId', (string) $otherMembership->id)
        ->call('update')
        ->assertHasErrors(['form.assigneeUserId']);
});
