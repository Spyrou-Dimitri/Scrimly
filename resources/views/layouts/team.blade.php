<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Scrimly' }} — {{ currentTeam()->name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
</head>

<body class="bg-bg-main text-white font-spaceGrotesk h-screen overflow-hidden
             flex flex-col
             lg:grid lg:grid-cols-[auto_1fr] lg:grid-rows-[auto_1fr]">
    <h1 class="sr-only">Scrimly</h1>

    <livewire:layout.sidebar />

    <livewire:layout.topbar />

    <main class="flex-1 overflow-y-auto bg-bg-main max-w-[1600px] w-full  mx-auto lg:col-start-2 lg:row-start-2">
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