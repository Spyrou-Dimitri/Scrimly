<?php

use App\Enums\Language;
use App\Enums\LolGoal;
use App\Enums\LolServeur;
use App\Enums\RoleInGame;
use App\Enums\RoleInTeam;
use App\Enums\StatusInTeam;
use App\Models\Subtask;
use App\Models\Task;
use App\Models\TaskFile;
use App\Models\TaskLink;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Livewire;

beforeEach(function (): void {
    App::setLocale('fr');
});

test('un joueur obtient 403 sur la page de création d\'un devoir', function (): void {
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

    $this->actingAs($player)->get($url)->assertForbidden();

    Livewire::actingAs($player)
        ->test('pages::tasks.create')
        ->assertForbidden();
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

test('soumission enregistre les fichiers via le job dispatchSync sur le disque public', function (): void {
    Storage::fake('public');

    $creator = User::factory()->create();
    $slug = 'equipe-tasks-files-'.Str::random(8);
    $team = Team::create([
        'name' => 'Équipe tâches fichiers',
        'slug' => $slug,
        'tag' => 'TFI',
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

    $pdf = UploadedFile::fake()->create('coach.pdf', 200, 'application/pdf');
    $image = UploadedFile::fake()->image('vod.png');

    $due = now()->addDay()->toDateString();

    Livewire::actingAs($coach)
        ->test('pages::tasks.create')
        ->set('form.title', 'Devoir avec fichiers')
        ->set('form.dueDate', $due)
        ->set('form.assigneeUserId', (string) $assigneeMembership->id)
        ->set('form.subtasks', [
            ['title' => 'Sous-tâche fichiers', 'is_completed' => false],
        ])
        ->set('form.files', [$pdf, $image])
        ->call('store')
        ->assertHasNoErrors()
        ->assertRedirect(route('tasks.index', ['slug' => $team->slug]));

    $task = Task::query()->where('title', 'Devoir avec fichiers')->first();

    expect($task)->not->toBeNull();

    $files = TaskFile::query()->where('task_id', $task->id)->orderBy('id')->get();

    expect($files)->toHaveCount(2)
        ->and($files->pluck('file_name')->all())->toBe(['coach.pdf', 'vod.png'])
        ->and($files->first()->uploaded_by)->toBe($coach->id);

    foreach ($files as $file) {
        Storage::disk('public')->assertExists($file->file_path);
        expect($file->file_path)->toStartWith(config('taskFiles.original_path').'/'.$task->id.'/');
    }
});

test('soumission enregistre les liens vidéo en base avec un titre optionnel', function (): void {
    $creator = User::factory()->create();
    $slug = 'equipe-tasks-links-'.Str::random(8);
    $team = Team::create([
        'name' => 'Équipe tâches liens',
        'slug' => $slug,
        'tag' => 'TLI',
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
        ->assertRedirect(route('tasks.index', ['slug' => $team->slug]));

    $task = Task::query()->where('title', 'Devoir avec liens')->first();

    $links = TaskLink::query()->where('task_id', $task->id)->orderBy('id')->get();

    expect($links)->toHaveCount(2)
        ->and($links[0]->url)->toBe('https://youtu.be/abc123')
        ->and($links[0]->title)->toBe('VOD finale Game 1')
        ->and($links[1]->url)->toBe('https://twitch.tv/exemple')
        ->and($links[1]->title)->toBeNull();
});

test('upload via newFiles empile les fichiers dans form.files et reset l input', function (): void {
    $creator = User::factory()->create();
    $slug = 'equipe-tasks-newfiles-'.Str::random(8);
    $team = Team::create([
        'name' => 'Équipe newFiles',
        'slug' => $slug,
        'tag' => 'TNF',
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

    $component = Livewire::actingAs($coach)
        ->test('pages::tasks.create')
        ->set('newFiles', [
            UploadedFile::fake()->create('first.pdf', 50, 'application/pdf'),
        ]);

    expect($component->get('form.files'))->toHaveCount(1)
        ->and($component->get('newFiles'))->toBe([])
        ->and($component->get('fileInputResetKey'))->toBe(1);

    $component->set('newFiles', [
        UploadedFile::fake()->create('second.pdf', 50, 'application/pdf'),
        UploadedFile::fake()->image('third.png'),
    ]);

    expect($component->get('form.files'))->toHaveCount(3)
        ->and($component->get('newFiles'))->toBe([])
        ->and($component->get('fileInputResetKey'))->toBe(2);
});

test('removeFile retire un fichier du tableau form.files par son index', function (): void {
    $creator = User::factory()->create();
    $slug = 'equipe-tasks-remove-file-'.Str::random(8);
    $team = Team::create([
        'name' => 'Équipe remove file',
        'slug' => $slug,
        'tag' => 'TRF',
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

    $component = Livewire::actingAs($coach)
        ->test('pages::tasks.create')
        ->set('form.files', [
            UploadedFile::fake()->create('one.pdf', 50, 'application/pdf'),
            UploadedFile::fake()->create('two.pdf', 50, 'application/pdf'),
        ])
        ->call('removeFile', 0);

    $remainingFiles = $component->get('form.files');

    expect($remainingFiles)->toHaveCount(1)
        ->and($remainingFiles[0]->getClientOriginalName())->toBe('two.pdf');
});

test('addLink puis removeLink gèrent le tableau form.links', function (): void {
    $creator = User::factory()->create();
    $slug = 'equipe-tasks-remove-link-'.Str::random(8);
    $team = Team::create([
        'name' => 'Équipe remove link',
        'slug' => $slug,
        'tag' => 'TRL',
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

    $component = Livewire::actingAs($coach)
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
