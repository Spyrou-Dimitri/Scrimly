<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @php
    $title = __('layouts/choose_a_team.default_title');
    @endphp
    @include('partials.head')

</head>

@php
$currentUser = auth()->user();
@endphp

<body
    class="min-h-screen flex flex-col bg-bg-main text-text-primary font-sans">
    <a href="#main-content"
        class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:top-4 focus:left-4 focus:rounded-lg focus:bg-bg-widget focus:px-4 focus:py-2 focus:text-white focus:shadow-basic">
        {{ __('accessibility.skip_to_content') }}
    </a>

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

        <nav class="flex items-center gap-6" aria-label="{{ __('layouts/choose_a_team.navigation_title') }}">
            <h2 class="sr-only">{{ __('layouts/choose_a_team.navigation_title') }}</h2>
            @if ($currentUser)
            <div class="flex items-center gap-2 lg:gap-3">
                <a href="{{ route('profile.show') }}"
                    title="{{ __('layouts/team.edit_profile_cta_title') }}"
                    aria-label="{{ __('layouts/team.edit_profile_cta_title') }} : {{ $currentUser->username }}"
                    class="flex items-center gap-2 lg:gap-3 group">

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

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button
                        type="submit"
                        title="{{ __('layouts/team.logout') }}"
                        aria-label="{{ __('layouts/team.logout_aria') }}"
                        class="cta-danger cta-danger--outline rounded-full size-10">
                        <flux:icon name="power" class="size-6" />
                    </button>
                </form>
            </div>
            @endif

        </nav>
    </header>

    <main id="main-content" aria-label="{{ __('accessibility.main_content') }}" class="flex-1 flex max-w-[1600px] mx-auto w-full flex-col items-center justify-center py-12 px-6">
        {{ $slot }}
    </main>

    <footer aria-label="{{ __('layouts/choose_a_team.footer_label') }}" class="flex bg-bg-widget shadow-basic flex-col md:flex-row md:items-center md:justify-between gap-2 px-8 py-6 text-sm text-text-secondary">
        <p>
            &copy; {{ date('Y') }} {!! __('layouts/choose_a_team.copyright') !!}
        </p>

        <div class="flex items-center gap-6">
            <x-cta href="#" :title="__('layouts/choose_a_team.mentions_legales_title')" :class="'nav'">{{ __('layouts/choose_a_team.mentions_legales') }}</x-cta>
            <x-cta href="#" :title="__('layouts/choose_a_team.conditions_utilisation_title')" :class="'nav'">{{ __('layouts/choose_a_team.conditions_utilisation') }}</x-cta>
        </div>
    </footer>
    <livewire:widgets::toast />
    <livewire:widgets::modal />
</body>

</html>