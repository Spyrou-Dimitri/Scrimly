<?php

test('la page d accueil s affiche sans erreur javascript', function () {
    visit('/')
        ->assertSee('Scrimly')
        ->assertNoJavaScriptErrors();
});

test('la page de connexion est accessible', function () {
    visit('/login')
        ->assertSee('Connexion')
        ->assertNoJavaScriptErrors();
});
