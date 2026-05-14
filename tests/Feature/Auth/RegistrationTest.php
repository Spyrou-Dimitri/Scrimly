<?php

use App\Models\User;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::registration());
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
    $response->assertSee(__('register/register.preview_placeholder'));
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
        ->assertRedirect(route('team.index', absolute: false));

    $this->assertAuthenticated();

    $this->assertDatabaseHas('users', [
        'username' => 'Faker',
        'email' => 'faker@example.com',
    ]);

    $user = User::query()->where('email', 'faker@example.com')->first();
    expect($user)->not->toBeNull();
    $this->assertDatabaseMissing('riot_profiles', ['user_id' => $user->id]);
});

test('new users can register with a valid riot tag', function () {
    Http::fake(function (Request $request) {
        $url = $request->url();

        if (str_contains($url, 'accounts/by-riot-id')) {
            return Http::response([
                'puuid' => 'b8834037-9959-4886-b704-6ee4e27d0c2e',
                'gameName' => 'HideOnBush',
                'tagLine' => 'KR1',
            ], 200);
        }

        if (str_contains($url, 'entries/by-puuid')) {
            return Http::response([[
                'queueType' => 'RANKED_SOLO_5x5',
                'tier' => 'CHALLENGER',
                'rank' => 'I',
                'leaguePoints' => 42,
                'wins' => 100,
                'losses' => 50,
            ]], 200);
        }

        if (str_contains($url, 'matches/by-puuid')) {
            return Http::response([], 200);
        }

        return Http::response(['error' => 'unmocked'], 404);
    });

    $response = $this->post(route('register.store'), [
        'username' => 'HideOnBush',
        'riot_tag' => 'HideOnBush#KR1',
        'email' => 'hideonbush@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('team.index', absolute: false));

    $this->assertAuthenticated();

    $this->assertDatabaseHas('users', [
        'username' => 'HideOnBush',
        'email' => 'hideonbush@example.com',
    ]);

    $user = User::query()->where('email', 'hideonbush@example.com')->first();
    expect($user)->not->toBeNull();
    $this->assertDatabaseHas('riot_profiles', [
        'user_id' => $user->id,
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
