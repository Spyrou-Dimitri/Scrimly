<?php

use App\Enums\DayOfTheWeek;
use App\Enums\RoleInGame;
use App\Enums\RoleInTeam;
use App\Enums\StatusInTeam;
use App\Models\PlayerDefaultSchedule;
use App\Models\RiotMatch;
use App\Models\RiotProfile;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\Support\TeamPlayer;

beforeEach(function (): void {
    App::setLocale('fr');
});

test('l’événement refresh_default_schedules recharge les créneaux sur l’onglet disponibilités', function (): void {
    $player = TeamPlayer::create();

    PlayerDefaultSchedule::query()->create([
        'team_member_id' => $player->member->id,
        'day_of_week' => DayOfTheWeek::MONDAY->value,
        'start_time' => '10:30',
        'end_time' => '11:45',
    ]);

    $component = Livewire::actingAs($player->user)
        ->test('tabs::roster.availability', ['teamMember' => $player->member]);

    $component->assertSuccessful()
        ->assertSee('10:30')
        ->assertSee('11:45');

    PlayerDefaultSchedule::query()->where('team_member_id', $player->member->id)->update([
        'start_time' => '14:15',
        'end_time' => '15:30',
    ]);

    $component->assertSee('10:30');

    $component->dispatch('refresh_default_schedules');

    $component->assertSee('14:15')
        ->assertSee('15:30')
        ->assertDontSee('10:30')
        ->assertDontSee('11:45');
});

test('le modal de créneaux habituels affiche le titre et les libellés des jours', function (): void {
    $player = TeamPlayer::create();

    Livewire::actingAs($player->user)
        ->test('modals::edit-availabilities', ['model_id' => $player->member->id])
        ->assertSuccessful()
        ->assertSee(__('modals/edit-availabilities.title'))
        ->assertSee(__('enums/day-of-the-week.monday'))
        ->assertSee(__('modals/edit-availabilities.save'));
});

test('le modal créneaux habituels refuse une heure de fin avant le début', function (): void {
    $player = TeamPlayer::create();

    $monday = (string) DayOfTheWeek::MONDAY->value;

    Livewire::actingAs($player->user)
        ->test('modals::edit-availabilities', ['model_id' => $player->member->id])
        ->set('form.slotEnabled.'.$monday, true)
        ->set('form.startTimes.'.$monday, '18:00')
        ->set('form.endTimes.'.$monday, '13:00')
        ->call('saveAvailabilities')
        ->assertHasErrors(['form.endTimes.'.$monday]);
});

test('le modal créneaux habituels refuse un jour coché sans heure de début', function (): void {
    $player = TeamPlayer::create();

    $monday = (string) DayOfTheWeek::MONDAY->value;

    Livewire::actingAs($player->user)
        ->test('modals::edit-availabilities', ['model_id' => $player->member->id])
        ->set('form.slotEnabled.'.$monday, true)
        ->set('form.startTimes.'.$monday, '')
        ->set('form.endTimes.'.$monday, '23:00')
        ->call('saveAvailabilities')
        ->assertHasErrors(['form.startTimes.'.$monday]);
});

test('ouvrir le modal disponibilités dispatch l’événement open_modal', function (): void {
    $player = TeamPlayer::create();

    Livewire::actingAs($player->user)
        ->test('tabs::roster.availability', ['teamMember' => $player->member])
        ->call('openModalAddAvailability')
        ->assertDispatched('open_modal');
});

test('la page profil roster affiche le membre et les libellés', function (): void {
    $player = TeamPlayer::create();

    Livewire::actingAs($player->user)
        ->test('pages::roster.show', ['slug' => $player->team->slug, 'id' => $player->member->id])
        ->assertSuccessful()
        ->assertSee($player->user->username)
        ->assertSee(__('pages/roster/show.role_in_game_label'))
        ->assertSee(__('pages/roster/show.tabs.matches'));
});

test('l’onglet historique des parties affiche une partie synchronisée', function (): void {
    $player = TeamPlayer::create();

    $riotProfile = RiotProfile::factory()->create([
        'user_id' => $player->user->id,
    ]);

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

    Livewire::actingAs($player->user)
        ->test('pages::roster.show', ['slug' => $player->team->slug, 'id' => $player->member->id])
        ->assertSuccessful()
        ->assertSee('Camille')
        ->assertSee('12 / 2 / 8')
        ->assertSee('28:45')
        ->assertSee(__('pages/roster/show.matches.win'))
        ->assertSee(__('pages/roster/show.matches.section_title'));
});

test('la page profil roster répond 404 pour un membre d\'une autre équipe', function (): void {
    $player = TeamPlayer::create();

    $otherPlayer = TeamPlayer::create();

    $foreignMember = TeamMember::create([
        'team_id' => $otherPlayer->team->id,
        'user_id' => User::factory()->create()->id,
        'roleInTeam' => RoleInTeam::PLAYER,
        'roleInGame' => RoleInGame::TOP,
        'is_starter' => true,
        'status' => StatusInTeam::ACCEPTED,
        'joined_at' => now(),
    ]);

    expect(fn () => Livewire::actingAs($player->user)->test('pages::roster.show', [
        'slug' => $player->team->slug,
        'id' => $foreignMember->id,
    ]))->toThrow(ModelNotFoundException::class);
});
