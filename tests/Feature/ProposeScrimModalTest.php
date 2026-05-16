<?php

use App\Enums\Language;
use App\Enums\LolGoal;
use App\Enums\LolServeur;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Str;
use Livewire\Livewire;

test('la modal propose-scrim affiche le formulaire et lie les champs', function (): void {
    $creator = User::factory()->create();
    $team = Team::create([
        'name' => 'G2 esport',
        'slug' => Str::random(10),
        'tag' => Str::upper(Str::random(4)),
        'language' => Language::FR,
        'server' => LolServeur::EUW,
        'goal' => LolGoal::FUN,
        'creator_id' => $creator->id,
    ]);

    Livewire::test('modals::scrims.propose-scrim', ['model_id' => $team->id])
        ->assertSet('team.id', $team->id)
        ->assertSee(__('modals/scrims/propose-scrim.submit'))
        ->set('scrimDate', '2026-05-20')
        ->set('scrimTime', '14:30')
        ->set('gameCount', '7')
        ->set('message', 'Dispo BO5')
        ->assertSet('scrimDate', '2026-05-20')
        ->assertSet('scrimTime', '14:30')
        ->assertSet('gameCount', '7')
        ->assertSet('message', 'Dispo BO5');
});
