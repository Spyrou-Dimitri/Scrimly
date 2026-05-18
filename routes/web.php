<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');
Route::view('/login', 'login')->name('login')->middleware('guest');
Route::view('/register', 'register')->name('register')->middleware('guest');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::livewire('team', 'pages::team.index')->name('team.index');
    Route::livewire('team/create', 'pages::team.create')->name('team.create');
    Route::livewire('team/join', 'pages::team.join')->name('team.join');
    Route::livewire('profile.show', 'pages::profile.show')->name('profile.show');

    Route::middleware('ensure.user.has.active.team')->group(function () {

        // Team
        Route::livewire('/{slug}/team/show/{id}', 'pages::team.show')->name('team.show');

        // Roster
        Route::livewire('/{slug}/roster', 'pages::roster.index')->name('roster.index');
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

    });
});

require __DIR__.'/settings.php';
