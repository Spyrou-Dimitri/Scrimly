<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ __('layouts/team.scrimly') }} — {{ currentTeam()->name }}@if (filled($title ?? null)) • {{ $title }}@endif</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon/favicon-96x96.png') }}" sizes="96x96">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon/favicon.svg') }}">
    <link rel="shortcut icon" href="{{ asset('favicon/favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicon/apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('favicon/site.webmanifest') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
</head>

<body class="bg-bg-main text-white font-spaceGrotesk h-screen overflow-hidden
             flex flex-col
             lg:grid lg:grid-cols-[auto_1fr] lg:grid-rows-[auto_1fr]">
    <a href="#main-content"
        class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:top-4 focus:left-4 focus:rounded-lg focus:bg-bg-widget focus:px-4 focus:py-2 focus:text-white focus:shadow-basic">
        {{ __('accessibility.skip_to_content') }}
    </a>

    <h1 class="sr-only">{{ __('layouts/team.scrimly') }}</h1>

    <livewire:layout.sidebar />

    <livewire:layout.topbar />

    <main
        id="main-content"
        aria-label="{{ __('accessibility.main_content') }}"
        x-init="$store.presence.connect(
        {{ currentTeam()->id }},
        {{ currentMember()->user_id }},
        @js([
            'isOnline' => __('pages/chats/index.is_online'),
            'isOffline' => __('pages/chats/index.is_offline'),
        ])
    )"
        class="flex-1 overflow-y-auto bg-bg-main max-w-[1600px] w-full  mx-auto lg:col-start-2 lg:row-start-2">
        <div class="px-6 py-8">
            {{ $slot }}
        </div>
    </main>


    @livewireScripts


    <div id="toast-container" class="fixed bottom-4 right-4 z-50"></div>
    <livewire:widgets::modal />
    <livewire:widgets::toast />


</body>

</html>