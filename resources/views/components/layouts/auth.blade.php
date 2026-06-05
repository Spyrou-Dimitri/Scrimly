<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Authentification' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300..700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-bg-main text-text-primary font-sans">
    <a href="#main-content"
        class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:top-4 focus:left-4 focus:rounded-lg focus:bg-bg-widget focus:px-4 focus:py-2 focus:text-white focus:shadow-basic">
        {{ __('accessibility.skip_to_content') }}
    </a>

    {{ $slot }}
</body>

</html>