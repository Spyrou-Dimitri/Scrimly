<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')

</head>

@php
$currentUser = auth()->user();
@endphp

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
            @if ($currentUser)
            <div class="flex items-center gap-2 lg:gap-3">
                <a href="{{ route('profile.show') }}"
                    title="{{ __('layouts/team.edit_profile_cta_title') }}"
                    class="flex items-center g ap-2 lg:gap-3 group">

                    <x-user-avatar
                        :user="$currentUser"
                        preset="topbar"
                        class="size-9 rounded-full object-cover flex-shrink-0"
                    />




                    <span class="hidden sm:inline-block relative text-white font-medium max-w-[160px]
                 before:content-[''] before:absolute before:bottom-0 before:left-0 
                 group-hover:text-gold
                 before:w-full before:h-[2px] before:bg-gold
                 before:scale-x-0 before:origin-left
                 before:transition-transform before:duration-150 before:ease-in-out 
                 group-hover:before:scale-x-100">
                        {{ $currentUser->username }}
                    </span>
                </a>
            </div>
            @endif

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
    <livewire:widgets::toast />
    <livewire:widgets::modal />
</body>

</html>