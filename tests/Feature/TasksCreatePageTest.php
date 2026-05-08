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
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

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
