<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @php
    $title = __('welcome.title');
    @endphp
    @include('partials.head')
</head>

@php
$currentUser = auth()->user();
@endphp

<body class="bg-bg-main text-text-primary font-sans">
    <header class="sticky top-0 z-20 shrink-0 flex items-center shadow-basic bg-bg-widget px-8 py-6">
        <h1 class="sr-only">
            ScrimlyLol
        </h1>
        <nav class="flex w-full items-center justify-between gap-8" aria-label="{{ __('welcome.navigation_title') }}">
            <h2 class="sr-only">
                {{ __('welcome.nav.title') }}
            </h2>
            <div class="flex items-center gap-8">
                <a href="{{ route('home') }}" class="text-gold text-2xl font-bold shrink-0">
                    Scrimly
                </a>

                <div class="hidden md:flex items-center gap-6">
                    <x-cta
                        href="#fonctionnalites"
                        :title="__('welcome.nav.features_title')"
                        :class="'nav'">
                        {{ __('welcome.nav.features') }}
                    </x-cta>

                    <x-cta
                        href="#how-it-works"
                        :title="__('welcome.nav.how_it_works_title')"
                        :class="'nav'">
                        {{ __('welcome.nav.how_it_works') }}
                    </x-cta>
                </div>
            </div>

            <div class="flex items-center gap-6">
                @if ($currentUser)
                <a href="{{ route('profile.show') }}"
                    title="{{ __('layouts/team.edit_profile_cta_title') }}"
                    class="flex items-center gap-2 lg:gap-3 group">

                    <x-user-avatar
                        :user="$currentUser"
                        preset="topbar"
                        class="size-9 rounded-full object-cover shrink-0" />

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
                        class="cta-danger cta-danger--outline rounded-full size-10">
                        <flux:icon name="power" class="size-6" />
                    </button>
                </form>
                @else
                <x-cta
                    href="{{ route('login') }}"
                    :title="__('welcome.nav.login_title')"
                    :class="'nav'">
                    {{ __('welcome.nav.login') }}
                </x-cta>

                <x-cta
                    href="{{ route('register') }}"
                    :title="__('welcome.nav.register_title')"
                    :class="'secondary'">
                    {{ __('welcome.nav.register') }}
                </x-cta>
                @endif
            </div>
        </nav>
    </header>

    <main class="max-w-[1600px] mx-auto">
        <section id="hero" class="relative h-[calc(100dvh-5.5rem)] overflow-hidden">
            <picture class="absolute inset-0 block h-full w-full">
                <source media="(min-width: 2000px)" srcset="{{ asset('img/welcome/landing/Bg-2000.jpg') }}">
                <source media="(min-width: 1600px)" srcset="{{ asset('img/welcome/landing/Bg-1600.jpg') }}">
                <source media="(min-width: 1200px)" srcset="{{ asset('img/welcome/landing/Bg-1200.jpg') }}">
                <source media="(min-width: 800px)" srcset="{{ asset('img/welcome/landing/Bg-800.jpg') }}">
                <img src="{{ asset('img/welcome/landing/Bg-400.jpg') }}" alt="" class="h-full w-full object-cover" aria-hidden="true">
            </picture>

            <div class="flex flex-col px-8 py-10 text-center gap-6 absolute origin-center top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 max-w-3xl backdrop-blur-[1px]">
                <div class="relative z-10 pb-6 border-b border-white/60 flex flex-col  items-center gap-6">
                    <div class="text-center">
                        <h2 class="text-5xl font-bold text-gold flex flex-col items-center justify-center gap-2">
                            {{ __('welcome.hero.title') }}
                            <span class="block text-4xl font-bold text-white leading-none">
                                {{ __('welcome.hero.slogan') }}
                            </span>
                        </h2>

                    </div>
                    <p class="text-xl text-center">
                        {{ __('welcome.hero.description') }}
                        <span class="block">
                            {{ __('welcome.hero.description_2') }}
                        </span>
                    </p>
                    <div class="flex flex-wrap items-center justify-center gap-4">
                        <x-cta
                            href="{{ route('register') }}"
                            :title="__('welcome.hero.cta_register_title')"
                            :class="'primary'">
                            {{ __('welcome.hero.cta_register') }}
                        </x-cta>
                        <x-cta
                            href="{{ route('login') }}"
                            :title="__('welcome.hero.cta_login_title')"
                            :class="'secondary'">
                            {{ __('welcome.hero.cta_login') }}
                        </x-cta>
                    </div>

                </div>
                <div>
                    <ul class="flex flex-row items-center justify-between gap-4">
                        <li class="text-gold flex flex-row items-center gap-1">
                            <flux:icon name="user-group" class="size-6" />
                            <span class="text-text-secondary">
                                {{ $numbersTeams }}
                            </span>
                            <span class="text-text-secondary">
                                {{ __('welcome.hero.teams') }}
                            </span>
                        </li>
                        <li class="text-gold flex flex-row items-center gap-1">
                            <flux:icon name="user" class="size-6" />
                            <span class="text-text-secondary">
                                {{ $numbersUsers }}
                            </span>
                            <span class="text-text-secondary">
                                {{ __('welcome.hero.users') }}
                            </span>
                        </li>
                        <li class="text-gold flex flex-row items-center gap-1">
                            <flux:icon name="trophy" class="size-6" />
                            <span class="text-text-secondary">
                                {{ $numbersScrims }}
                            </span>
                            <span class="text-text-secondary">
                                {{ __('welcome.hero.scrims') }}
                            </span>
                        </li>
                    </ul>
                </div>
            </div>

        </section>
        <section id="fonctionnalites" class="scroll-mt-[5.5rem] py-12 px-6 flex flex-col items-center justify-center gap-10">
            <div class="flex flex-col items-center justify-center gap-4">
                <h2 class="text-[40px] font-bold text-center leading-none">
                    {!! __('welcome.Features.title') !!}
                </h2>
                <p class="text-center text-xl text-text-secondary">
                    {{ __('welcome.Features.slogan') }}
                </p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <x-cards.feature
                    icon="calendar-days"
                    title="{{ __('welcome.Features.cards.calendar.title') }}"
                    description="{{ __('welcome.Features.cards.calendar.description') }}" />
                <x-cards.feature
                    icon="book-open"
                    title="{{ __('welcome.Features.cards.tasks.title') }}"
                    description="{{ __('welcome.Features.cards.tasks.description') }}" />
                <x-cards.feature
                    icon="user-group"
                    title="{{ __('welcome.Features.cards.roster.title') }}"
                    description="{{ __('welcome.Features.cards.roster.description') }}" />
                <x-cards.feature
                    icon="chart-bar"
                    title="{{ __('welcome.Features.cards.followData.title') }}"
                    description="{{ __('welcome.Features.cards.followData.description') }}" />
                <x-cards.feature
                    icon="chat-bubble-left-right"
                    title="{{ __('welcome.Features.cards.chat.title') }}"
                    description="{{ __('welcome.Features.cards.chat.description') }}" />
                <x-cards.feature
                    icon="check-circle"
                    title="{{ __('welcome.Features.cards.Checklist.title') }}"
                    description="{{ __('welcome.Features.cards.Checklist.description') }}" />
            </div>

        </section>
        <section id="how-it-works" class="bg-bg-widget py-12 px-6 flex flex-col items-center justify-center gap-10">
            <div class="flex flex-col items-center justify-center gap-4">
                <h2 class="text-[40px] font-bold text-center leading-none">
                    {!! __('welcome.how-it-works.title') !!}
                </h2>
                <p class="text-center text-xl text-text-secondary">
                    {{ __('welcome.how-it-works.description') }}
                </p>
            </div>
            <ul class="flex flex-col gap-20">
                <li>
                    <x-text-media
                        :number="'1'"
                        :title="(__('welcome.how-it-works.steps.invite_join.title'))"
                        :description="(__('welcome.how-it-works.steps.invite_join.description'))">
                        <x-slot:media>
                            <picture class="block w-full">
                                <source media="(min-width: 1400px)" srcset="{{ asset('img/welcome/how-it-work/first-step/create-800.jpg') }}">
                                <source media="(min-width: 1000px)" srcset="{{ asset('img/welcome/how-it-work/first-step/create-600.jpg') }}">
                                <source media="(min-width: 768px)" srcset="{{ asset('img/welcome/how-it-work/first-step/create-800.jpg') }}">
                                <source media="(min-width: 530px)" srcset="{{ asset('img/welcome/how-it-work/first-step/create-800.jpg') }}">
                                <img src="{{ asset('img/welcome/how-it-work/first-step/create-480.jpg') }}" alt="" class="w-full aspect-video object-cover" aria-hidden="true">
                            </picture>
                        </x-slot:media>
                    </x-text-media>
                </li>
                <li>
                    <x-text-media
                        number="2"
                        title="{{ __('welcome.how-it-works.steps.manage_players.title') }}"
                        description="{{ __('welcome.how-it-works.steps.manage_players.description') }}"
                        reverse>
                        <x-slot:media>
                            <picture class="block w-full">
                                <source media="(min-width: 1400px)" srcset="{{ asset('img/welcome/how-it-work/second-step/roster-800.jpg') }}">
                                <source media="(min-width: 1000px)" srcset="{{ asset('img/welcome/how-it-work/second-step/roster-600.jpg') }}">
                                <source media="(min-width: 768px)" srcset="{{ asset('img/welcome/how-it-work/second-step/roster-800.jpg') }}">
                                <source media="(min-width: 530px)" srcset="{{ asset('img/welcome/how-it-work/second-step/roster-800.jpg') }}">
                                <img src="{{ asset('img/welcome/how-it-work/second-step/roster-800.jpg') }}" alt="" class="w-full aspect-video object-cover" aria-hidden="true">
                            </picture>
                        </x-slot:media>
                    </x-text-media>
                </li>
                <li>
                    <x-text-media
                        number="3"
                        title="{{ __('welcome.how-it-works.steps.plan_scrims.title') }}"
                        description="{{ __('welcome.how-it-works.steps.plan_scrims.description') }}">
                        <x-slot:media>
                            <picture class="block w-full">
                                <source media="(min-width: 1400px)" srcset="{{ asset('img/welcome/how-it-work/third-step/manage-800.jpg') }}">
                                <source media="(min-width: 1000px)" srcset="{{ asset('img/welcome/how-it-work/third-step/manage-600.jpg') }}">
                                <source media="(min-width: 768px)" srcset="{{ asset('img/welcome/how-it-work/third-step/manage-800.jpg') }}">
                                <source media="(min-width: 530px)" srcset="{{ asset('img/welcome/how-it-work/third-step/manage-800.jpg') }}">
                                <img src="{{ asset('img/welcome/how-it-work/third-step/manage-800.jpg') }}" alt="" class="w-full aspect-video object-cover" aria-hidden="true">
                            </picture>
                        </x-slot:media>
                    </x-text-media>
                </li>
            </ul>
        </section>

    </main>
</body>

</html>