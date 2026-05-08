<?php

use App\Enums\Language;
use App\Enums\LolGoal;
use App\Enums\LolServeur;
use App\Enums\RoleInGame;
use App\Enums\RoleInTeam;
use App\Enums\StatusInTeam;
use App\Models\Subtask;
use App\Models\Task;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Livewire\Livewire;

beforeEach(function (): void {
    App::setLocale('fr');
});

test('la page création de devoir affiche les blocs du formulaire et les joueurs PLAYERS acceptés', function (): void {
    $creator = User::factory()->create();
    $slug = 'equipe-tasks-create-'.Str::random(8);
    $team = Team::create([
        'name' => 'Équipe tâches création',
        'slug' => $slug,
        'tag' => 'TCR',
        'language' => Language::FR,
        'server' => LolServeur::EUW,
        'goal' => LolGoal::FUN,
        'creator_id' => $creator->id,
    ]);

    $coachMemberUser = User::factory()->create([
        'username' => 'coach_fixture_tasks_create',
        'current_team_id' => $team->id,
    ]);

    TeamMember::create([
        'team_id' => $team->id,
        'user_id' => $coachMemberUser->id,
        'roleInTeam' => RoleInTeam::COACH,
        'roleInGame' => null,
        'is_starter' => false,
        'status' => StatusInTeam::ACCEPTED,
        'joined_at' => now(),
    ]);

    $player = User::factory()->create([
        'username' => 'joueur_fixture_tasks_create',
        'current_team_id' => $team->id,
    ]);

    TeamMember::create([
        'team_id' => $team->id,
        'user_id' => $player->id,
        'roleInTeam' => RoleInTeam::PLAYER,
        'roleInGame' => RoleInGame::MID,
        'is_starter' => true,
        'status' => StatusInTeam::ACCEPTED,
        'joined_at' => now(),
    ]);

    $url = route('tasks.create', ['slug' => $team->slug]);

    $response = $this->actingAs($player)->get($url);

    $response->assertSuccessful()
        ->assertSeeText(__('pages/tasks/create.main_legend'))
        ->assertSeeText(__('pages/tasks/create.subtasks_legend'))
        ->assertSeeText(__('pages/tasks/create.resources_legend'))
        ->assertSeeText(__('pages/tasks/create.field_player'))
        ->assertSeeText(__('pages/tasks/create.field_due_date'))
        ->assertSee('type="date"', escape: false)
        ->assertSeeText('joueur_fixture_tasks_create')
        ->assertDontSeeText('coach_fixture_tasks_create');
});

test('soumission du formulaire de création enregistre la tâche et les sous-tâches dans task_subtasks', function (): void {
    $creator = User::factory()->create();
    $slug = 'equipe-tasks-store-'.Str::random(8);
    $team = Team::create([
        'name' => 'Équipe tâches store',
        'slug' => $slug,
        'tag' => 'TSR',
        'language' => Language::FR,
        'server' => LolServeur::EUW,
        'goal' => LolGoal::FUN,
        'creator_id' => $creator->id,
    ]);

    $coach = User::factory()->create([
        'username' => 'coach_fixture_tasks_store',
        'current_team_id' => $team->id,
    ]);

    TeamMember::create([
        'team_id' => $team->id,
        'user_id' => $coach->id,
        'roleInTeam' => RoleInTeam::COACH,
        'roleInGame' => null,
        'is_starter' => false,
        'status' => StatusInTeam::ACCEPTED,
        'joined_at' => now(),
    ]);

    $assigneePlayer = User::factory()->create([
        'username' => 'assignee_fixture_tasks_store',
        'current_team_id' => $team->id,
    ]);

    $assigneeMembership = TeamMember::create([
        'team_id' => $team->id,
        'user_id' => $assigneePlayer->id,
        'roleInTeam' => RoleInTeam::PLAYER,
        'roleInGame' => RoleInGame::MID,
        'is_starter' => true,
        'status' => StatusInTeam::ACCEPTED,
        'joined_at' => now(),
    ]);

    $due = now()->addDay()->toDateString();

    Livewire::actingAs($coach)
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
        ->assertRedirect(route('tasks.index', ['slug' => $team->slug]));

    $task = Task::query()->first();

    expect($task)->not->toBeNull()
        ->and($task->title)->toBe('Mon devoir titre ABC')
        ->and($task->deadline->toDateString())->toBe($due)
        ->and($task->team_member_id)->toBe($assigneeMembership->id);

    $subtasks = Subtask::query()->where('task_id', $task->id)->orderBy('id')->get();

    expect($subtasks)->toHaveCount(2)
        ->and($subtasks->pluck('title')->all())->toBe(['Sous-tâche une', 'Sous-tâche deux XYZ']);
});

test('soumission avec description vide enregistre la tâche sans erreur de validation', function (): void {
    $creator = User::factory()->create();
    $slug = 'equipe-tasks-empty-desc-'.Str::random(8);
    $team = Team::create([
        'name' => 'Équipe desc vide',
        'slug' => $slug,
        'tag' => 'TDV',
        'language' => Language::FR,
        'server' => LolServeur::EUW,
        'goal' => LolGoal::FUN,
        'creator_id' => $creator->id,
    ]);

    $coach = User::factory()->create([
        'current_team_id' => $team->id,
    ]);

    TeamMember::create([
        'team_id' => $team->id,
        'user_id' => $coach->id,
        'roleInTeam' => RoleInTeam::COACH,
        'roleInGame' => null,
        'is_starter' => false,
        'status' => StatusInTeam::ACCEPTED,
        'joined_at' => now(),
    ]);

    $assigneePlayer = User::factory()->create([
        'current_team_id' => $team->id,
    ]);

    $assigneeMembership = TeamMember::create([
        'team_id' => $team->id,
        'user_id' => $assigneePlayer->id,
        'roleInTeam' => RoleInTeam::PLAYER,
        'roleInGame' => RoleInGame::MID,
        'is_starter' => true,
        'status' => StatusInTeam::ACCEPTED,
        'joined_at' => now(),
    ]);

    $due = now()->addDay()->toDateString();

    Livewire::actingAs($coach)
        ->test('pages::tasks.create')
        ->set('form.title', 'Tâche sans description')
        ->set('form.description', '')
        ->set('form.dueDate', $due)
        ->set('form.assigneeUserId', (string) $assigneeMembership->id)
        ->set('form.subtasks', [
            ['title' => 'Sous-tâche unique', 'is_completed' => false],
        ])
        ->call('store')
        ->assertHasNoErrors()
        ->assertRedirect(route('tasks.index', ['slug' => $team->slug]));

    $task = Task::query()->where('title', 'Tâche sans description')->first();

    expect($task)->not->toBeNull()
        ->and($task->description)->toBeNull();
});
