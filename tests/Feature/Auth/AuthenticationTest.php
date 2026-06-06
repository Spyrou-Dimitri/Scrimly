<?php

use App\Models\User;

test('login screen can be rendered', function () {
    $response = $this->get(route('login'));

    $response->assertOk();
});

test('authenticated users are redirected away from the login screen', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('login'));

    $response->assertRedirect(route('team.index', absolute: false));
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('team.index', absolute: false));

    $this->assertAuthenticatedAs($user);
});

test('users cannot authenticate with invalid password', function () {
    $user = User::factory()->create();

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $response->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('users cannot authenticate with unknown email', function () {
    $response = $this->post(route('login.store'), [
        'email' => 'unknown@example.com',
        'password' => 'password',
    ]);

    $response->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('email and password are required to authenticate', function () {
    $response = $this->post(route('login.store'), [
        'email' => '',
        'password' => '',
    ]);

    $response->assertSessionHasErrors(['email', 'password']);

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('logout'));

    $response->assertRedirect(route('home'));

    $this->assertGuest();
});
