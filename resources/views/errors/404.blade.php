<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @php
    $title = __('errors/404.title');
    @endphp
    @include('partials.head')
</head>

<body class="bg-bg-main text-text-primary min-h-screen flex items-center justify-center px-4">
    <main>
        <section class="flex flex-col items-center text-center max-w-lg">
            <h2 class="text-gold font-bold text-[64px] leading-none" aria-hidden="true">
                404
            </h2>


            <h1 class="sr-only">
                {{ __('errors/404.title') }}
            </h1>

            <img
                src="{{ asset('img/blitzcrankEmote.png') }}"
                alt="{{ __('errors/404.image_alt') }}"
                class="w-48 h-48 object-contain mb-8"
                width="192"
                height="192" />

            <p class="text-2xl mb-8">
                {{ __('errors/404.message') }}
            </p>

            <button
                type="button"
                onclick="history.back()"
                class="cta-primary"
                title="{{ __('errors/404.back_title') }}">
                {{ __('errors/404.back') }}
            </button>
        </section>

    </main>
</body>

</html>