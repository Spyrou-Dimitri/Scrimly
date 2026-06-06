<?php

use App\Enums\AbsenceJustification;
use App\Models\Absence;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\Support\TeamPlayer;

test('la modal edit-absence préremplit la date au format attendu par un input date', function (): void {
    $player = TeamPlayer::create();

    $absence = Absence::create([
        'team_member_id' => $player->member->id,
        'date' => '2026-05-03',
        'justification' => AbsenceJustification::MEDICAL,
    ]);

    Livewire::actingAs($player->user)
        ->test('modals::absence.edit-absence', ['model_id' => $absence->id])
        ->assertSet('form.date', '2026-05-03')
        ->assertSet('form.justification', AbsenceJustification::MEDICAL);
});

test('la modal edit-absence met à jour une absence lorsque le formulaire est valide', function (): void {
    Carbon::setTestNow('2026-05-07 15:00:00');

    $yesterday = '2026-05-06';
    $today = '2026-05-07';

    $player = TeamPlayer::create();

    $absence = Absence::create([
        'team_member_id' => $player->member->id,
        'date' => $yesterday,
        'justification' => AbsenceJustification::MEDICAL,
    ]);

    Livewire::actingAs($player->user)
        ->test('modals::absence.edit-absence', ['model_id' => $absence->id])
        ->set('form.date', $today)
        ->assertSet('form.date', $today)
        ->set('form.justification', AbsenceJustification::EXAM)
        ->call('editAbsence');

    $absence->refresh();

    expect($absence->date->toDateString())->toBe($today)
        ->and($absence->justification)->toBe(AbsenceJustification::EXAM);
});
