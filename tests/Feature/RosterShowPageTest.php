<?php

use App\Enums\DayOfTheWeek;
use App\Enums\Language;
use App\Enums\LolGoal;
use App\Enums\LolServeur;
use App\Enums\RoleInGame;
use App\Enums\RoleInTeam;
use App\Enums\StatusInTeam;
use App\Models\PlayerDefaultSchedule;
use App\Models\RiotMatch;
use App\Models\RiotProfile;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Livewire\Livewire;

beforeEach(function (): void {
    App::setLocale('fr');
});

test('le paramètre d’URL tab=availability affiche la section disponibilités', function (): void {
    $creator = User::factory()->create();
    $team = Team::create([
        'name' => 'Équipe disponibilités',
        'slug' => 'equipe-dispo-'.Str::random(8),
        'tag' => 'DSP',
        'language' => Language::FR,
        'server' => LolServeur::EUW,
        'goal' => LolGoal::FUN,
        'creator_id' => $creator->id,
    ]);

    $player = User::factory()->create([
        'username' => 'joueur_dispo_tab',
        'current_team_id' => $team->id,
    ]);

    $teamMember = TeamMember::create([
        'team_id' => $team->id,
        'user_id' => $player->id,
        'roleInTeam' => RoleInTeam::PLAYER,
        'roleInGame' => RoleInGame::MID,
        'is_starter' => true,
        'status' => StatusInTeam::ACCEPTED,
        'joined_at' => now(),
    ]);

    $url = route('roster.show', ['slug' => $team->slug, 'id' => $teamMember->id]).'?tab=availability';

    $response = $this->actingAs($player)->get($url);

    $response->assertSuccessful()
        ->assertSee(__('pages/roster/show.availability.section_title'), escape: false)
        ->assertDontSee(__('pages/roster/show.matches.section_title'), escape: false);
});

test('l’événement refresh_default_schedules recharge les créneaux sur l’onglet disponibilités', function (): void {
    $creator = User::factory()->create();
    $team = Team::create([
        'name' => 'Équipe refresh créneaux',
        'slug' => 'equipe-refresh-creneaux-'.Str::random(8),
        'tag' => 'RFC',
        'language' => Language::FR,
        'server' => LolServeur::EUW,
        'goal' => LolGoal::FUN,
        'creator_id' => $creator->id,
    ]);

    $player = User::factory()->create([
        'username' => 'joueur_refresh_creneaux',
        'current_team_id' => $team->id,
    ]);

    $teamMember = TeamMember::create([
        'team_id' => $team->id,
        'user_id' => $player->id,
        'roleInTeam' => RoleInTeam::PLAYER,
        'roleInGame' => RoleInGame::MID,
        'is_starter' => true,
        'status' => StatusInTeam::ACCEPTED,
        'joined_at' => now(),
    ]);

    PlayerDefaultSchedule::query()->create([
        'team_member_id' => $teamMember->id,
        'day_of_week' => DayOfTheWeek::MONDAY->value,
        'start_time' => '10:30',
        'end_time' => '11:45',
    ]);

    $component = Livewire::actingAs($player)
        ->test('tabs::roster.availability', ['teamMember' => $teamMember]);

    $component->assertSuccessful()
        ->assertSee('10:30', escape: false)
        ->assertSee('11:45', escape: false);

    PlayerDefaultSchedule::query()->where('team_member_id', $teamMember->id)->update([
        'start_time' => '14:15',
        'end_time' => '15:30',
    ]);

    $component->assertSee('10:30', escape: false);

    $component->dispatch('refresh_default_schedules');

    $component->assertSee('14:15', escape: false)
        ->assertSee('15:30', escape: false)
        ->assertDontSee('10:30', escape: false)
        ->assertDontSee('11:45', escape: false);
});

test('le modal de créneaux habituels affiche le titre et les libellés des jours', function (): void {
    $creator = User::factory()->create();
    $team = Team::create([
        'name' => 'Équipe modal créneaux',
        'slug' => 'equipe-creneaux-'.Str::random(8),
        'tag' => 'CRN',
        'language' => Language::FR,
        'server' => LolServeur::EUW,
        'goal' => LolGoal::FUN,
        'creator_id' => $creator->id,
    ]);

    $player = User::factory()->create([
        'username' => 'joueur_creneaux_modal',
        'current_team_id' => $team->id,
    ]);

    $teamMember = TeamMember::create([
        'team_id' => $team->id,
        'user_id' => $player->id,
        'roleInTeam' => RoleInTeam::PLAYER,
        'roleInGame' => RoleInGame::MID,
        'is_starter' => true,
        'status' => StatusInTeam::ACCEPTED,
        'joined_at' => now(),
    ]);

    Livewire::actingAs($player)
        ->test('modals::edit-availabilities', ['model_id' => $teamMember->id])
        ->assertSuccessful()
        ->assertSee(__('modals/edit-availabilities.title'), escape: false)
        ->assertSee(__('modals/edit-availabilities.monday'), escape: false)
        ->assertSee(__('modals/edit-availabilities.save'), escape: false);
});

test('le modal créneaux habituels refuse une heure de fin avant le début', function (): void {
    $creator = User::factory()->create();
    $team = Team::create([
        'name' => 'Équipe validation fin avant début',
        'slug' => 'equipe-val-fin-'.Str::random(8),
        'tag' => 'VFD',
        'language' => Language::FR,
        'server' => LolServeur::EUW,
        'goal' => LolGoal::FUN,
        'creator_id' => $creator->id,
    ]);

    $player = User::factory()->create([
        'username' => 'joueur_val_fin',
        'current_team_id' => $team->id,
    ]);

    $teamMember = TeamMember::create([
        'team_id' => $team->id,
        'user_id' => $player->id,
        'roleInTeam' => RoleInTeam::PLAYER,
        'roleInGame' => RoleInGame::MID,
        'is_starter' => true,
        'status' => StatusInTeam::ACCEPTED,
        'joined_at' => now(),
    ]);

    $monday = (string) DayOfTheWeek::MONDAY->value;

    Livewire::actingAs($player)
        ->test('modals::edit-availabilities', ['model_id' => $teamMember->id])
        ->set('form.slotEnabled.'.$monday, true)
        ->set('form.startTimes.'.$monday, '18:00')
        ->set('form.endTimes.'.$monday, '13:00')
        ->call('saveAvailabilities')
        ->assertHasErrors(['form.endTimes.'.$monday]);
});

test('le modal créneaux habituels refuse un jour coché sans heure de début', function (): void {
    $creator = User::factory()->create();
    $team = Team::create([
        'name' => 'Équipe validation heure manquante',
        'slug' => 'equipe-val-heure-'.Str::random(8),
        'tag' => 'VHM',
        'language' => Language::FR,
        'server' => LolServeur::EUW,
        'goal' => LolGoal::FUN,
        'creator_id' => $creator->id,
    ]);

    $player = User::factory()->create([
        'username' => 'joueur_val_heure',
        'current_team_id' => $team->id,
    ]);

    $teamMember = TeamMember::create([
        'team_id' => $team->id,
        'user_id' => $player->id,
        'roleInTeam' => RoleInTeam::PLAYER,
        'roleInGame' => RoleInGame::MID,
        'is_starter' => true,
        'status' => StatusInTeam::ACCEPTED,
        'joined_at' => now(),
    ]);

    $monday = (string) DayOfTheWeek::MONDAY->value;

    Livewire::actingAs($player)
        ->test('modals::edit-availabilities', ['model_id' => $teamMember->id])
        ->set('form.slotEnabled.'.$monday, true)
        ->set('form.startTimes.'.$monday, '')
        ->set('form.endTimes.'.$monday, '23:00')
        ->call('saveAvailabilities')
        ->assertHasErrors(['form.startTimes.'.$monday]);
});

test('ouvrir le modal disponibilités dispatch l’événement open_modal', function (): void {
    $creator = User::factory()->create();
    $team = Team::create([
        'name' => 'Équipe modal disponibilités',
        'slug' => 'equipe-modal-dispo-'.Str::random(8),
        'tag' => 'DMD',
        'language' => Language::FR,
        'server' => LolServeur::EUW,
        'goal' => LolGoal::FUN,
        'creator_id' => $creator->id,
    ]);

    $player = User::factory()->create([
        'username' => 'joueur_modal_dispo',
        'current_team_id' => $team->id,
    ]);

    $teamMember = TeamMember::create([
        'team_id' => $team->id,
        'user_id' => $player->id,
        'roleInTeam' => RoleInTeam::PLAYER,
        'roleInGame' => RoleInGame::MID,
        'is_starter' => true,
        'status' => StatusInTeam::ACCEPTED,
        'joined_at' => now(),
    ]);

    Livewire::actingAs($player)
        ->test('tabs::roster.availability', ['teamMember' => $teamMember])
        ->call('openModalAddAvailability')
        ->assertDispatched('open_modal');
});

test('la page profil roster affiche le membre et les libellés', function (): void {
    $creator = User::factory()->create();
    $team = Team::create([
        'name' => 'Équipe test',
        'slug' => 'equipe-test-roster-show-'.Str::random(8),
        'tag' => 'TST',
        'language' => Language::FR,
        'server' => LolServeur::EUW,
        'goal' => LolGoal::FUN,
        'creator_id' => $creator->id,
    ]);

    $player = User::factory()->create([
        'username' => 'joueur_roster_show',
        'current_team_id' => $team->id,
    ]);

    $teamMember = TeamMember::create([
        'team_id' => $team->id,
        'user_id' => $player->id,
        'roleInTeam' => RoleInTeam::PLAYER,
        'roleInGame' => RoleInGame::MID,
        'is_starter' => true,
        'status' => StatusInTeam::ACCEPTED,
        'joined_at' => now(),
    ]);

    Livewire::actingAs($player)
        ->test('pages::roster.show', ['slug' => $team->slug, 'id' => $teamMember->id])
        ->assertSuccessful()
        ->assertSee('joueur_roster_show', escape: false)
        ->assertSee(__('pages/roster/show.role_in_game_label'), escape: false)
        ->assertSee(__('pages/roster/show.tabs.matches'), escape: false);
});

test('l’onglet historique des parties affiche une partie synchronisée', function (): void {
    $creator = User::factory()->create();
    $team = Team::create([
        'name' => 'Équipe match history',
        'slug' => 'equipe-match-history-'.Str::random(8),
        'tag' => 'TMH',
        'language' => Language::FR,
        'server' => LolServeur::EUW,
        'goal' => LolGoal::FUN,
        'creator_id' => $creator->id,
    ]);

    $player = User::factory()->create([
        'username' => 'joueur_match_history',
        'current_team_id' => $team->id,
    ]);

    $teamMember = TeamMember::create([
        'team_id' => $team->id,
        'user_id' => $player->id,
        'roleInTeam' => RoleInTeam::PLAYER,
        'roleInGame' => RoleInGame::MID,
        'is_starter' => true,
        'status' => StatusInTeam::ACCEPTED,
        'joined_at' => now(),
    ]);

    $riotProfile = RiotProfile::factory()->for($player)->create();

    RiotMatch::query()->create([
        'riot_profile_id' => $riotProfile->id,
        'match_id' => 'EUW1_TEST_MATCH_'.Str::random(6),
        'game_duration' => 1725,
        'played_at' => now()->subHours(2),
        'champion_name' => 'Camille',
        'champion_id' => 164,
        'champion_level' => 14,
        'role' => 'TOP',
        'win' => true,
        'kills' => 12,
        'deaths' => 2,
        'assists' => 8,
        'cs' => 248,
        'items' => [3031, 3074, 0, 0, 0, 0, 3340],
    ]);

    Livewire::actingAs($player)
        ->test('pages::roster.show', ['slug' => $team->slug, 'id' => $teamMember->id])
        ->assertSuccessful()
        ->assertSee('Camille', escape: false)
        ->assertSee('12 / 2 / 8', escape: false)
        ->assertSee('28:45', escape: false)
        ->assertSee(__('pages/roster/show.matches.win'), escape: false)
        ->assertSee(__('pages/roster/show.matches.section_title'), escape: false);
});

test('la page profil roster répond 404 pour un membre d\'une autre équipe', function (): void {
    $creator = User::factory()->create();
    $team = Team::create([
        'name' => 'Équipe A',
        'slug' => 'equipe-a-roster-'.Str::random(8),
        'tag' => 'AAA',
        'language' => Language::FR,
        'server' => LolServeur::EUW,
        'goal' => LolGoal::FUN,
        'creator_id' => $creator->id,
    ]);

    $otherCreator = User::factory()->create();
    $otherTeam = Team::create([
        'name' => 'Équipe B',
        'slug' => 'equipe-b-roster-'.Str::random(8),
        'tag' => 'BBB',
        'language' => Language::FR,
        'server' => LolServeur::EUW,
        'goal' => LolGoal::FUN,
        'creator_id' => $otherCreator->id,
    ]);

    $player = User::factory()->create([
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

    $foreignMember = TeamMember::create([
        'team_id' => $otherTeam->id,
        'user_id' => User::factory()->create()->id,
        'roleInTeam' => RoleInTeam::PLAYER,
        'roleInGame' => RoleInGame::TOP,
        'is_starter' => true,
        'status' => StatusInTeam::ACCEPTED,
        'joined_at' => now(),
    ]);

    expect(fn () => Livewire::actingAs($player)->test('pages::roster.show', [
        'slug' => $team->slug,
        'id' => $foreignMember->id,
    ]))->toThrow(ModelNotFoundException::class);
});
