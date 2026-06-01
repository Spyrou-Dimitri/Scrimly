<?php

use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WelcomeController::class, 'index'])->name('home');
Route::view('/login', 'login')->name('login')->middleware('guest');
Route::view('/register', 'register')->name('register')->middleware('guest');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboardddd', 'dashboard')->name('dashboarddddd');
    Route::livewire('team', 'pages::team.index')->name('team.index');
    Route::livewire('team/create', 'pages::team.create')->name('team.create');
    Route::livewire('team/join', 'pages::team.join')->name('team.join');
    Route::livewire('profile.show', 'pages::profile.show')->name('profile.show');

    Route::middleware('ensure.user.has.active.team')->group(function () {

        // Dashboard
        Route::livewire('/{slug}/dashboard', 'pages::dashboard')->name('dashboard');

        // Team
        Route::livewire('/{slug}/team/show/{id}', 'pages::team.show')->name('team.show');
        Route::livewire('/{slug}/team/edit/{id}', 'pages::team.edit')->name('team.edit');

        // Roster
        Route::livewire('/{slug}/roster', 'pages::roster.index')->name('roster.index');
        Route::livewire('/{slug}/roster/invitations', 'pages::roster.invitations')->name('roster.invitations.create');
        Route::livewire('/{slug}/roster/{id}', 'pages::roster.show')->name('roster.show');

        // Devoir
        Route::livewire('/{slug}/tasks', 'pages::tasks.index')->name('tasks.index');
        Route::livewire('/{slug}/tasks/create', 'pages::tasks.create')->name('tasks.create');
        Route::livewire('/{slug}/tasks/edit/{id}', 'pages::tasks.edit')->name('tasks.edit');
        Route::livewire('/{slug}/tasks/{id}', 'pages::tasks.show')->name('tasks.show');

        // Scrims
        Route::livewire('/{slug}/scrims', 'pages::scrims.index')->name('scrims.index');
        Route::livewire('/{slug}/scrims/find', 'pages::scrims.find')->name('scrims.find');
        Route::livewire('/{slug}/scrims/{id}', 'pages::scrims.show')->name('scrims.show');
        Route::livewire('/{slug}/scrims/{id}/games/create', 'pages::scrims.games.create')->name('scrims.games.create');
        Route::livewire('/{slug}/scrims/{id}/games/edit/{gameId}', 'pages::scrims.games.edit')->name('scrims.games.edit');

        // Calendrier
        Route::livewire('/{slug}/calendar', 'pages::calendar.index')->name('calendar.index');

        // Chats
        Route::livewire('/{slug}/chats', 'pages::chats.index')->name('chats.index');
    });
});

require __DIR__.'/settings.php';
