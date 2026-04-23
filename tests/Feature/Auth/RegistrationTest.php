<?php

use App\Models\User;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::registration());
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('new users can register without a riot tag', function () {
    $response = $this->post(route('register.store'), [
        'username' => 'Faker',
        'email' => 'faker@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();

    $this->assertDatabaseHas('users', [
        'username' => 'Faker',
        'email' => 'faker@example.com',
        'riot_tag' => null,
    ]);
});

test('new users can register with a valid riot tag', function () {
    $response = $this->post(route('register.store'), [
        'username' => 'HideOnBush',
        'riot_tag' => 'HideOnBush#KR1',
        'email' => 'hideonbush@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();

    $this->assertDatabaseHas('users', [
        'username' => 'HideOnBush',
        'riot_tag' => 'HideOnBush#KR1',
    ]);
});

test('registration requires username, email and password', function () {
    $response = $this->post(route('register.store'), []);

    $response->assertSessionHasErrors(['username', 'email', 'password']);

    $this->assertGuest();
});

test('registration rejects an already used email', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    $response = $this->post(route('register.store'), [
        'username' => 'NewUser',
        'email' => 'taken@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('registration rejects an already used username', function () {
    User::factory()->create(['username' => 'Faker']);

    $response = $this->post(route('register.store'), [
        'username' => 'Faker',
        'email' => 'other@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasErrors('username');

    $this->assertGuest();
});

test('registration rejects a malformed riot tag', function () {
    $response = $this->post(route('register.store'), [
        'username' => 'NewUser',
        'riot_tag' => 'not-a-valid-tag',
        'email' => 'new@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasErrors('riot_tag');

    $this->assertGuest();
});
