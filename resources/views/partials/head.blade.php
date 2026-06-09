<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<meta name="author" content="Dimitri Spyrou">
<meta name="description" content="{{ __('layouts/seo.description') }}">
<meta name="keywords" content="{{ __('layouts/seo.keywords') }}">

<title>
    {{ filled($title ?? null) ? config('app.name', 'Laravel').' - '.$title : config('app.name', 'Laravel') }}
</title>

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">


<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300..700&display=swap" rel="stylesheet">

<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet">

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
