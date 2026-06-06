<?php

use App\Enums\RoleInGame;
use App\Enums\RoleInTeam;
use App\Enums\StatusInTeam;
use App\Models\Subtask;
use App\Models\Task;
use App\Models\TaskLink;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Support\Facades\App;
use Livewire\Livewire;
use Tests\Support\TeamPlayer;

beforeEach(function (): void {
    App::setLocale('fr');
});

test('un joueur obtient 403 sur la page de création d\'un devoir', function (): void {
    $player = TeamPlayer::create();
    $player->member->roleInTeam = RoleInTeam::PLAYER;
    $player->member->save();
    $url = route('tasks.create', ['slug' => $player->team->slug]);

    $this->actingAs($player->user)->get($url)->assertForbidden();

    Livewire::actingAs($player->user)
        ->test('pages::tasks.create')
        ->assertForbidden();
});

test('soumission du formulaire de création enregistre la tâche et les sous-tâches dans task_subtasks', function (): void {
    $coach = TeamPlayer::create();
    $coach->member->update([
        'roleInTeam' => RoleInTeam::COACH,
        'roleInGame' => null,
        'is_starter' => false,
    ]);

    $assigneePlayer = User::factory()->create([
        'username' => 'assignee_fixture_tasks_store',
        'current_team_id' => $coach->team->id,
    ]);

    $assigneeMembership = TeamMember::create([
        'team_id' => $coach->team->id,
        'user_id' => $assigneePlayer->id,
        'roleInTeam' => RoleInTeam::PLAYER,
        'roleInGame' => RoleInGame::MID,
        'is_starter' => true,
        'status' => StatusInTeam::ACCEPTED,
        'joined_at' => now(),
    ]);

    $due = now()->addDay()->toDateString();

    Livewire::actingAs($coach->user)
        ->test('pages::tasks.create')
        ->set('form.title', 'Mon devoir titre ABC')
        ->set('form.description', 'Description min trois')
        ->set('form.dueDate', $due)
        ->set('form.assigneeUserId', (string) $assigneeMembership->id)
        ->set('form.subtasks', [
            ['title' => 'Sous-tâche une', 'is_completed' => false],
            ['title' => 'Sous-tâche deux XYZ', 'is_completed' => false],
        ])
        ->call('store')
        ->assertRedirect(route('tasks.index', ['slug' => $coach->team->slug]));

    $task = Task::query()->first();

    expect($task)->not->toBeNull()
        ->and($task->title)->toBe('Mon devoir titre ABC')
        ->and($task->deadline->toDateString())->toBe($due)
        ->and($task->team_member_id)->toBe($assigneeMembership->id);

    $subtasks = Subtask::query()->where('task_id', $task->id)->orderBy('id')->get();

    expect($subtasks)->toHaveCount(2)
        ->and($subtasks->pluck('title')->all())->toBe(['Sous-tâche une', 'Sous-tâche deux XYZ']);
});

test('soumission enregistre les liens vidéo en base avec un titre optionnel', function (): void {
    $coach = TeamPlayer::create();
    $coach->member->update([
        'roleInTeam' => RoleInTeam::COACH,
        'roleInGame' => null,
        'is_starter' => false,
    ]);

    $assigneePlayer = User::factory()->create([
        'current_team_id' => $coach->team->id,
    ]);

    $assigneeMembership = TeamMember::create([
        'team_id' => $coach->team->id,
        'user_id' => $assigneePlayer->id,
        'roleInTeam' => RoleInTeam::PLAYER,
        'roleInGame' => RoleInGame::MID,
        'is_starter' => true,
        'status' => StatusInTeam::ACCEPTED,
        'joined_at' => now(),
    ]);

    $due = now()->addDay()->toDateString();

    Livewire::actingAs($coach->user)
        ->test('pages::tasks.create')
        ->set('form.title', 'Devoir avec liens')
        ->set('form.dueDate', $due)
        ->set('form.assigneeUserId', (string) $assigneeMembership->id)
        ->set('form.subtasks', [
            ['title' => 'Sous-tâche liens', 'is_completed' => false],
        ])
        ->set('form.links', [
            ['url' => 'https://youtu.be/abc123', 'title' => 'VOD finale Game 1'],
            ['url' => 'https://twitch.tv/exemple', 'title' => null],
        ])
        ->call('store')
        ->assertHasNoErrors()
        ->assertRedirect(route('tasks.index', ['slug' => $coach->team->slug]));

    $task = Task::query()->where('title', 'Devoir avec liens')->first();

    $links = TaskLink::query()->where('task_id', $task->id)->orderBy('id')->get();

    expect($links)->toHaveCount(2)
        ->and($links[0]->url)->toBe('https://youtu.be/abc123')
        ->and($links[0]->title)->toBe('VOD finale Game 1')
        ->and($links[1]->url)->toBe('https://twitch.tv/exemple')
        ->and($links[1]->title)->toBeNull();
});

test('addLink puis removeLink gèrent le tableau form.links', function (): void {
    $coach = TeamPlayer::create();
    $coach->member->update([
        'roleInTeam' => RoleInTeam::COACH,
        'roleInGame' => null,
        'is_starter' => false,
    ]);

    $component = Livewire::actingAs($coach->user)
        ->test('pages::tasks.create')
        ->set('newLinkUrl', 'https://youtu.be/abc')
        ->set('newLinkTitle', 'VOD')
        ->call('addLink')
        ->set('newLinkUrl', 'https://twitch.tv/exemple')
        ->set('newLinkTitle', '')
        ->call('addLink');

    expect($component->get('form.links'))->toHaveCount(2);

    $component->call('removeLink', 0);

    $remainingLinks = $component->get('form.links');

    expect($remainingLinks)->toHaveCount(1)
        ->and($remainingLinks[0]['url'])->toBe('https://twitch.tv/exemple');
});
