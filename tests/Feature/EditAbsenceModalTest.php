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
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Livewire\Livewire;

test('la modal edit-absence préremplit la date au format attendu par un input date', function (): void {
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

    $absence = Absence::create([
        'team_member_id' => $teamMember->id,
        'date' => '2026-05-03',
        'justification' => AbsenceJustification::MEDICAL,
    ]);

    Livewire::test('modals::absence.edit-absence', ['model_id' => $absence->id])
        ->assertSet('form.date', '2026-05-03')
        ->assertSet('form.justification', AbsenceJustification::MEDICAL);
});

test('la modal edit-absence met à jour une absence lorsque le formulaire est valide', function (): void {
    Carbon::setTestNow('2026-05-07 15:00:00');

    $yesterday = '2026-05-06';
    $today = '2026-05-07';

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

    $absence = Absence::create([
        'team_member_id' => $teamMember->id,
        'date' => $yesterday,
        'justification' => AbsenceJustification::MEDICAL,
    ]);

    Livewire::test('modals::absence.edit-absence', ['model_id' => $absence->id])
        ->set('form.date', $today)
        ->assertSet('form.date', $today)
        ->set('form.justification', AbsenceJustification::EXAM)
        ->call('editAbsence');

    $absence->refresh();

    expect($absence->date->toDateString())->toBe($today)
        ->and($absence->justification)->toBe(AbsenceJustification::EXAM);
});
