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

<body class="bg-bg-main text-text-primary">
    <a href="#hero-heading"
        class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:top-4 focus:left-4 focus:rounded-lg focus:bg-bg-widget focus:px-4 focus:py-2 focus:text-white focus:shadow-basic">
        {{ __('welcome.skip_to_content') }}
    </a>

    <header class="sticky top-0 z-20 shrink-0 flex items-center shadow-basic bg-bg-widget px-8 py-6">
        <h1 class="sr-only">
            ScrimlyLol
        </h1>
        <nav class="flex w-full items-center justify-between gap-8" aria-label="{{ __('welcome.navigation_title') }}">
            <h2 class="sr-only">
                {{ __('welcome.nav.title') }}
            </h2>
            <div class="flex items-center gap-8">
                <a href="{{ route('home') }}"
                    aria-label="{{ __('welcome.nav.home_title') }}"
                    aria-current="page"
                    class="text-gold text-2xl font-bold shrink-0">
                    Scrimly
                </a>

                <div class="hidden md:flex items-center gap-6">
                    <x-cta
                        href="#fonctionnalites"
                        :title="__('welcome.nav.features_title')"
                        :class="'nav'"
                        aria-label="{{ __('welcome.nav.features_title') }}">
                        {{ __('welcome.nav.features') }}
                    </x-cta>

                    <x-cta
                        href="#how-it-works"
                        :title="__('welcome.nav.how_it_works_title')"
                        :class="'nav'"
                        aria-label="{{ __('welcome.nav.how_it_works_title') }}">
                        {{ __('welcome.nav.how_it_works') }}
                    </x-cta>
                </div>
            </div>

            <div class="flex items-center gap-6">
                @if ($currentUser)
                <a href="{{ route('profile.show') }}"
                    title="{{ __('layouts/team.edit_profile_cta_title') }}"
                    aria-label="{{ __('layouts/team.edit_profile_cta_title') }} : {{ $currentUser->username }}"
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
                        aria-label="{{ __('layouts/team.logout') }}"
                        class="cta-danger cta-danger--outline rounded-full size-10">
                        <flux:icon name="power" class="size-6" aria-hidden="true" />
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

    <main>
        <section id="hero"
            aria-labelledby="hero-heading"
            itemscope
            itemtype="https://schema.org/SoftwareApplication"
            class="relative h-[calc(100dvh-5.5rem)] overflow-hidden bg-bg-main">
            <picture class="absolute inset-0 block h-full w-full">
                <source media="(min-width: 1600px)" srcset="{{ asset('img/welcome/landing/Bg-2000.jpg') }}">
                <source media="(min-width: 1200px)" srcset="{{ asset('img/welcome/landing/Bg-1600.jpg') }}">
                <source media="(min-width: 800px)" srcset="{{ asset('img/welcome/landing/Bg-1200.jpg') }}">
                <img src="{{ asset('img/welcome/landing/Bg-2000.jpg') }}" alt="" class="h-full w-full object-cover" aria-hidden="true">
            </picture>

            <div class="relative z-10 mx-auto flex h-full w-full max-w-[1600px] items-center justify-center px-6 py-10">
                <div class="flex w-full max-w-3xl flex-col gap-6 text-center backdrop-blur-[1px]">
                    <div class="relative z-10 pb-6 border-b border-white/60 flex flex-col  items-center gap-6">
                        <div class="text-center">
                            <h2
                                id="hero-heading"
                                itemprop="name"
                                class="welcome-heading-hero text-gold flex flex-col items-center justify-center gap-2">
                                {{ __('welcome.hero.title') }}
                                <span class="welcome-heading-hero-sub block text-white">
                                    {{ __('welcome.hero.slogan') }}
                                </span>
                            </h2>

                        </div>
                        <p itemprop="description" class="welcome-body-lead text-center">
                            {{ __('welcome.hero.description') }}
                            <span class="block">
                                {{ __('welcome.hero.description_2') }}
                            </span>
                        </p>
                        <div role="group" aria-label="{{ __('welcome.hero.actions_label') }}" class="flex flex-wrap items-center justify-center gap-4">
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
                    <ul
                        itemprop="aggregateRating"
                        aria-label="{{ __('welcome.hero.stats_label') }}"
                        class="flex flex-row flex-wrap items-center justify-between gap-4">
                        <li
                            itemprop="contentRating"
                            aria-label="{{ $numbersTeams }} {{ __('welcome.hero.teams') }}"
                            class="text-gold flex flex-row items-center gap-1">
                            <flux:icon name="user-group" class="size-6" aria-hidden="true" />
                            <span class="text-text-secondary" aria-hidden="true">
                                {{ $numbersTeams }}
                            </span>
                            <span class="text-text-secondary" aria-hidden="true">
                                {{ __('welcome.hero.teams') }}
                            </span>
                        </li>
                        <li
                            itemprop="contentRating"
                            aria-label="{{ $numbersUsers }} {{ __('welcome.hero.users') }}"
                            class="text-gold flex flex-row items-center gap-1">
                            <flux:icon name="user" class="size-6" aria-hidden="true" />
                            <span class="text-text-secondary" aria-hidden="true">
                                {{ $numbersUsers }}
                            </span>
                            <span class="text-text-secondary" aria-hidden="true">
                                {{ __('welcome.hero.users') }}
                            </span>
                        </li>
                        <li
                            itemprop="contentRating"
                            aria-label="{{ $numbersScrims }} {{ __('welcome.hero.scrims') }}"
                            class="text-gold flex flex-row items-center gap-1">
                            <flux:icon name="trophy" class="size-6" aria-hidden="true" />
                            <span class="text-text-secondary" aria-hidden="true">
                                {{ $numbersScrims }}
                            </span>
                            <span class="text-text-secondary" aria-hidden="true">
                                {{ __('welcome.hero.scrims') }}
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </section>
        <section id="fonctionnalites"
            aria-labelledby="features-heading"
            class="scroll-mt-[5.5rem] bg-bg-main-2 py-12"
            itemscope
            itemtype="https://schema.org/ItemList">
            <div class="mx-auto flex w-full max-w-[1600px] flex-col items-center justify-center gap-10 px-6">
                <div class="flex flex-col items-center justify-center gap-4">
                    <h2
                        id="features-heading"
                        itemprop="name"
                        class="welcome-heading-section text-center">
                        {!! __('welcome.Features.title') !!}
                    </h2>
                    <p itemprop="description" class="welcome-body-lead text-center text-text-secondary">
                        {{ __('welcome.Features.slogan') }}
                    </p>
                </div>
                <ul role="list" aria-labelledby="features-heading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <x-cards.feature
                        role="listitem"
                        itemprop="itemListElement"
                        icon="calendar-days"
                        title="{{ __('welcome.Features.cards.calendar.title') }}"
                        description="{{ __('welcome.Features.cards.calendar.description') }}" />
                    <x-cards.feature
                        role="listitem"
                        itemprop="itemListElement"
                        icon="book-open"
                        title="{{ __('welcome.Features.cards.tasks.title') }}"
                        description="{{ __('welcome.Features.cards.tasks.description') }}" />
                    <x-cards.feature
                        role="listitem"
                        itemprop="itemListElement"
                        icon="user-group"
                        title="{{ __('welcome.Features.cards.roster.title') }}"
                        description="{{ __('welcome.Features.cards.roster.description') }}" />
                    <x-cards.feature
                        role="listitem"
                        itemprop="itemListElement"
                        icon="chart-bar"
                        title="{{ __('welcome.Features.cards.followData.title') }}"
                        description="{{ __('welcome.Features.cards.followData.description') }}" />
                    <x-cards.feature
                        role="listitem"
                        itemprop="itemListElement"
                        icon="chat-bubble-left-right"
                        title="{{ __('welcome.Features.cards.chat.title') }}"
                        description="{{ __('welcome.Features.cards.chat.description') }}" />
                    <x-cards.feature
                        role="listitem"
                        itemprop="itemListElement"
                        icon="check-circle"
                        title="{{ __('welcome.Features.cards.Checklist.title') }}"
                        description="{{ __('welcome.Features.cards.Checklist.description') }}" />
                </ul>
            </div>
        </section>
        <section
            id="how-it-works"
            aria-labelledby="how-it-works-heading"
            itemscope
            itemtype="https://schema.org/HowTo"
            class="bg-bg-main py-12">
            <div class="mx-auto flex w-full max-w-[1600px] flex-col items-center justify-center gap-10 px-6">
                <div class="flex flex-col items-center justify-center gap-4">
                    <h2
                        id="how-it-works-heading"
                        itemprop="name"
                        class="welcome-heading-section text-center">
                        {!! __('welcome.how-it-works.title') !!}
                    </h2>
                    <p itemprop="description"
                        class="welcome-body-lead text-center text-text-secondary">
                        {{ __('welcome.how-it-works.description') }}
                    </p>
                </div>
                <ul aria-label="{{ __('welcome.how-it-works.steps_label') }}" class="flex flex-col gap-20">
                    <li itemprop="step" itemscope itemtype="https://schema.org/HowToStep">
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
                    <li itemprop="step" itemscope itemtype="https://schema.org/HowToStep">
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
                    <li itemprop="step" itemscope itemtype="https://schema.org/HowToStep">
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
            </div>
        </section>
        <section id="invitation" aria-labelledby="invitation-heading" class="overflow-visible bg-bg-main-2 py-24 md:pb-32">
            <div class="mx-auto flex w-full max-w-[1200px] flex-col items-center justify-center gap-10 px-6">
                <div class="grid shadow-basic w-full grid-cols-1 gap-10 overflow-visible bg-bg-widget p-10 md:grid-cols-2">
                    <div class="flex flex-col items-start justify-center gap-4">
                        <h2 id="invitation-heading" class="welcome-heading-section">
                            {!! __('welcome.invitation.title') !!}
                        </h2>
                        <p class="text-text-secondary">
                            {{ __('welcome.invitation.description') }}
                        </p>
                        <x-cta
                            href="{{ route('register') }}"
                            :title="__('welcome.invitation.cta_title')"
                            :class="'primary'">
                            {{ __('welcome.invitation.cta') }}
                        </x-cta>
                    </div>
                    <div class="relative hidden md:block min-h-48 lg:min-h-64 overflow-visible">
                        <img
                            src="{{ asset('img/welcome/invitations/yasuo.png') }}"
                            class="absolute -bottom-10 -right-25 w-[135%] max-w-none h-auto pointer-events-none select-none"
                            alt=""
                            aria-hidden="true">
                    </div>
                </div>
            </div>
        </section>
        <footer
            aria-label="{{ __('welcome.footer.label') }}"
            itemscope
            itemtype="https://schema.org/Organization"
            class="bg-bg-widget py-6">
            <div class="mx-auto flex w-full max-w-[1600px] flex-row items-center justify-between gap-4 px-6">
                <p
                    itemprop="copyrightNotice"
                    class="w-full text-text-secondary">
                    {{ __('welcome.footer.Copyright') }}
                </p>
                <p

                    itemprop="creator"
                    itemscope
                    itemtype="https://schema.org/Person"
                    class="w-full text-right">
                    <span itemprop="name">
                        {!! __('welcome.footer.created_by', ['label' => __('welcome.footer.creator_link_label')]) !!}
                    </span>
                </p>
            </div>
        </footer>
    </main>
</body>

</html>