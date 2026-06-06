<?php

use App\Models\User;
use Livewire\Livewire;

test('profile page is displayed', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('profile.show'))
        ->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('pages::profile.show')
        ->set('form.username', 'UpdatedUser')
        ->set('form.email', 'updated@example.com')
        ->call('updateProfil')
        ->assertHasNoErrors();

    $user->refresh();

    expect($user->username)->toBe('UpdatedUser')
        ->and($user->email)->toBe('updated@example.com');
});
