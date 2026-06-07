<?php

test('la page d accueil s affiche sans erreur javascript', function () {
    visit('/')
        ->assertSee('Scrimly');
});

test('la page de connexion est accessible', function () {
    visit('/login')
        ->assertSee('Connexion');
});

test('la page d inscription est accessible', function () {
    visit('/register')
        ->assertSee('votre compte');
});
