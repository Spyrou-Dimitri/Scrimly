<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
    
</head>

<body
    class="min-h-screen flex flex-col bg-bg-main text-text-primary font-sans">
    <header class="flex items-center shadow-basic justify-between bg-bg-widget px-8 py-6">
        <h1 class="sr-only">
            <a href="{{ route('team.index') }}" wire:navigate>
                Scrimly
                <span class="sr-only"> - {{ $pageTitle ?? __('layouts/choose_a_team.default_title') }}</span>
            </a>
        </h1>
        <a href="{{ route('team.index') }}" class="text-gold text-2xl font-bold" wire:navigate>
            Scrimly
        </a>

        <nav class="flex items-center gap-6">
            <h2 class="sr-only">{{ __('layouts/choose_a_team.navigation_title') }}</h2>
            <x-cta href="#" :title="__('layouts/choose_a_team.mon_compte_title')" :class="'nav'">{{ __('layouts/choose_a_team.mon_compte') }}</x-cta>
        </nav>
    </header>

    <main class="flex-1 flex flex-col items-center justify-center py-12 px-6">
        {{ $slot }}
    </main>

    <footer class="flex bg-bg-widget shadow-basic flex-col md:flex-row md:items-center md:justify-between gap-2 px-8 py-6 text-sm text-text-secondary">
        <p>
            &copy; {{ date('Y') }} {!! __('layouts/choose_a_team.copyright') !!}
        </p>

        <div class="flex items-center gap-6">
            <x-cta href="#" :title="__('layouts/choose_a_team.mentions_legales_title')" :class="'nav'">Mentions légales</x-cta>
            <x-cta href="#" :title="__('layouts/choose_a_team.conditions_utilisation_title')" :class="'nav'">Conditions d'utilisation</x-cta>
        </div>
    </footer>

</body>

</html>