<?php

use App\Enums\Language;
use App\Enums\LolGoal;
use App\Enums\LolServeur;
use App\Enums\RoleInGame;
use App\Enums\RoleInTeam;
use App\Enums\StatusInTeam;
use App\Enums\StatusScrim;
use App\Enums\StatusScrimRequest;
use App\Models\Scrim;
use App\Models\ScrimRequest;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

beforeEach(function (): void {
    App::setLocale('fr');
});

test('la page de création de game répond avec succès et affiche le formulaire', function (): void {
    $creator = User::factory()->create();
    $team = Team::create([
        'name' => 'Équipe Alpha',
        'slug' => 'equipe-alpha-'.Str::random(8),
        'tag' => 'ALP',
        'language' => Language::FR,
        'server' => LolServeur::EUW,
        'goal' => LolGoal::FUN,
        'creator_id' => $creator->id,
    ]);

    $opponent = Team::create([
        'name' => 'Équipe Beta',
        'slug' => 'equipe-beta-'.Str::random(8),
        'tag' => 'BTA',
        'language' => Language::FR,
        'server' => LolServeur::EUW,
        'goal' => LolGoal::FUN,
        'creator_id' => $creator->id,
    ]);

    $member = User::factory()->create([
        'current_team_id' => $team->id,
    ]);

    TeamMember::create([
        'team_id' => $team->id,
        'user_id' => $member->id,
        'roleInTeam' => RoleInTeam::PLAYER,
        'roleInGame' => RoleInGame::MID,
        'is_starter' => true,
        'status' => StatusInTeam::ACCEPTED,
        'joined_at' => now(),
    ]);

    $scrimRequest = ScrimRequest::create([
        'status' => StatusScrimRequest::ACCEPTED,
        'scheduled_date' => now()->toDateString(),
        'scheduled_time' => now()->format('H:i:s'),
        'number_of_games' => 3,
        'requester_team_id' => $team->id,
        'receiver_team_id' => $opponent->id,
    ]);

    $scrim = Scrim::create([
        'scheduled_date' => now()->toDateString(),
        'scheduled_time' => now()->format('H:i:s'),
        'number_of_games' => 3,
        'status' => StatusScrim::SCHEDULED,
        'scrim_request_id' => $scrimRequest->id,
        'opponent_team_id' => $opponent->id,
        'team_id' => $team->id,
    ]);

    $url = route('scrims.games.create', ['slug' => $team->slug, 'id' => $scrim->id]);

    $response = $this->actingAs($member)->get($url);

    $response->assertSuccessful()
        ->assertSee(__('pages/scrims/games/create.page_title'), escape: false)
        ->assertSee(__('pages/scrims/games/create.main_fieldset_legend'), escape: false)
        ->assertSee(__('pages/scrims/games/create.scrim_label', ['teams' => 'Équipe Alpha vs Équipe Beta']), escape: false)
        ->assertSee(__('pages/scrims/games/create.result_win'), escape: false)
        ->assertSee(__('pages/scrims/games/create.result_loss'), escape: false);
});
