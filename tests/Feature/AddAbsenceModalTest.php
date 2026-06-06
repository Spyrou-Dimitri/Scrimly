<?php

use App\Enums\AbsenceJustification;
use App\Models\Absence;
use Livewire\Livewire;
use Tests\Support\TeamPlayer;

test('la modal add-absence crée une absence en base lorsque le formulaire est valide', function (): void {
    $player = TeamPlayer::create();
    $absenceDay = now()->addDays(15)->toDateString();

    Livewire::actingAs($player->user)
        ->test('modals::absence.add-absence', ['model_id' => $player->member->id])
        ->set('form.absenceDay', $absenceDay)
        ->set('form.justification', AbsenceJustification::MEDICAL)
        ->call('store');

    $absence = Absence::query()->first();

    expect($absence)->not->toBeNull()
        ->and($absence->team_member_id)->toBe($player->member->id)
        ->and($absence->date->toDateString())->toBe($absenceDay)
        ->and($absence->justification)->toBe(AbsenceJustification::MEDICAL);
});

test('la modal add-absence refuse une soumission sans données', function (): void {
    $player = TeamPlayer::create();

    Livewire::actingAs($player->user)
        ->test('modals::absence.add-absence', ['model_id' => $player->member->id])
        ->call('store')
        ->assertHasErrors(['form.absenceDay', 'form.justification']);

    expect(Absence::query()->count())->toBe(0);
});
