<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<meta name="author" content="Dimitri Spyrou">
<meta name="description" content="{{ __('layouts/seo.description') }}">
<meta name="keywords" content="{{ __('layouts/seo.keywords') }}">


<title>
    {{ filled($title ?? null) ? config('app.name', 'Laravel').' - '.$title : config('app.name', 'Laravel') }}
</title>

<link rel="icon" type="image/png" href="{{ asset('favicon/favicon-96x96.png') }}" sizes="96x96">
<link rel="icon" type="image/svg+xml" href="{{ asset('favicon/favicon.svg') }}">
<link rel="shortcut icon" href="{{ asset('favicon/favicon.ico') }}">
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicon/apple-touch-icon.png') }}">
<link rel="manifest" href="{{ asset('favicon/site.webmanifest') }}">


<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300..700&display=swap" rel="stylesheet">

<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet">

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
