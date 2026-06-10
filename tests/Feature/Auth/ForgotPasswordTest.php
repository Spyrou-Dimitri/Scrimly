<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;

test('forgot password screen can be rendered', function () {
    $response = $this->get(route('forgot-password'));

    $response->assertOk();
});

test('authenticated users are redirected away from the forgot password screen', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('forgot-password'));

    $response->assertRedirect(route('team.index', absolute: false));
});

test('users can request a password reset link', function () {
    Notification::fake();

    $user = User::factory()->create();

    $response = $this->post(route('password.email'), [
        'email' => $user->email,
    ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('home'));

    Notification::assertSentTo($user, ResetPassword::class);
});

test('email is required to request a password reset link', function () {
    $response = $this->post(route('password.email'), [
        'email' => '',
    ]);

    $response->assertSessionHasErrors('email');
});
