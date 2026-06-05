@props([
    'title' => null,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen flex flex-col bg-bg-main text-text-primary font-sans">
    <h1 class="sr-only">
        {{ $title }}
    </h1>

    {{ $slot }}

    @fluxScripts
</body>

</html>
