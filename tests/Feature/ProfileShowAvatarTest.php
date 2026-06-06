<?php

use App\Enums\DefaultAvatar;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('la page de profil personnalisé est accessible pour un utilisateur connecté', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('profile.show'))
        ->assertOk();
});

test('un avatar téléversé persisté est conservé tant que la grille de portraits par défaut ne remplace pas la sélection', function () {
    $user = User::factory()->create([
        'avatar_type' => 'upload',
        'avatar_value' => 'fixture.jpg',
    ]);

    Livewire::actingAs($user)
        ->test('pages::profile.show')
        ->call('updateProfil');

    $user->refresh();

    expect($user->avatar_type)->toBe('upload')
        ->and($user->avatar_value)->toBe('fixture.jpg');
});

test('un utilisateur avec avatar illustré peut en choisir un autre dans la grille', function () {
    $user = User::factory()->create([
        'avatar_type' => 'default',
        'avatar_value' => DefaultAvatar::CAMILLE->value,
    ]);

    Livewire::actingAs($user)
        ->test('pages::profile.show')
        ->call('choosePresetAvatar', DefaultAvatar::RYZE->value)
        ->call('updateProfil');

    $user->refresh();

    expect($user->avatar_type)->toBe('default')
        ->and($user->avatar_value)->toBe(DefaultAvatar::RYZE->value);
});

test('basculer vers un avatar illustré après un téléversement supprime les fichiers précédents', function () {
    Storage::fake('public');

    $filename = 'previous.jpg';

    Storage::disk('public')->put(config('avatar.original_path').'/'.$filename, 'Yahouuu');
    Storage::disk('public')->put(sprintf(config('avatar.variant_pattern'), 400, 400).'/'.$filename, 'Yahouuu');

    $user = User::factory()->create([
        'avatar_type' => 'upload',
        'avatar_value' => $filename,
    ]);

    Livewire::actingAs($user)
        ->test('pages::profile.show')
        ->call('choosePresetAvatar', DefaultAvatar::DIANA->value)
        ->call('updateProfil');

    $user->refresh();

    expect($user->avatar_type)->toBe('default')
        ->and($user->avatar_value)->toBe(DefaultAvatar::DIANA->value);

    Storage::disk('public')->assertMissing(config('avatar.original_path').'/'.$filename);
});
