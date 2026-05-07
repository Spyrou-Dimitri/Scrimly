<?php

use App\Enums\AbsenceJustification;
use App\Enums\Language;
use App\Enums\LolGoal;
use App\Enums\LolServeur;
use App\Enums\RoleInGame;
use App\Enums\RoleInTeam;
use App\Enums\StatusInTeam;
use App\Models\Absence;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Support\Str;
use Livewire\Livewire;

test('la modal add-absence crée une absence en base lorsque le formulaire est valide', function (): void {
    $creator = User::factory()->create();
    $team = Team::create([
        'name' => Str::random(10),
        'slug' => Str::random(10),
        'tag' => Str::upper(Str::random(4)),
        'language' => Language::FR,
        'server' => LolServeur::EUW,
        'goal' => LolGoal::FUN,
        'creator_id' => $creator->id,
    ]);

    $player = User::factory()->create(['current_team_id' => $team->id]);

    $teamMember = TeamMember::create([
        'team_id' => $team->id,
        'user_id' => $player->id,
        'roleInTeam' => RoleInTeam::PLAYER,
        'roleInGame' => RoleInGame::MID,
        'is_starter' => true,
        'status' => StatusInTeam::ACCEPTED,
        'joined_at' => now(),
    ]);

    Livewire::test('modals::absence.add-absence', ['model_id' => $teamMember->id])
        ->set('form.absenceDay', '2026-05-15')
        ->set('form.justification', AbsenceJustification::MEDICAL)
        ->call('store');

    $absence = Absence::query()->first();

    expect($absence)->not->toBeNull()
        ->and($absence->team_member_id)->toBe($teamMember->id)
        ->and($absence->date->toDateString())->toBe('2026-05-15')
        ->and($absence->justification)->toBe(AbsenceJustification::MEDICAL);
});

test('la modal add-absence refuse une soumission sans données', function (): void {
    $creator = User::factory()->create();
    $team = Team::create([
        'name' => Str::random(10),
        'slug' => Str::random(10),
        'tag' => Str::upper(Str::random(4)),
        'language' => Language::FR,
        'server' => LolServeur::EUW,
        'goal' => LolGoal::FUN,
        'creator_id' => $creator->id,
    ]);

    $player = User::factory()->create(['current_team_id' => $team->id]);

    $teamMember = TeamMember::create([
        'team_id' => $team->id,
        'user_id' => $player->id,
        'roleInTeam' => RoleInTeam::PLAYER,
        'roleInGame' => RoleInGame::MID,
        'is_starter' => true,
        'status' => StatusInTeam::ACCEPTED,
        'joined_at' => now(),
    ]);

    Livewire::test('modals::absence.add-absence', ['model_id' => $teamMember->id])
        ->call('store')
        ->assertHasErrors(['form.absenceDay', 'form.justification']);

    expect(Absence::query()->count())->toBe(0);
});
