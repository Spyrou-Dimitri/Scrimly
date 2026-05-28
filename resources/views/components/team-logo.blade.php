@props([
    'team',
    'preset' => 'thumbnail',
    'alt' => null,
    'loading' => null,
    'src' => null,
])

@php
    $sizesPresets = [
        'thumbnail' => '40px',
        'topbar' => '36px',
        'team-switcher' => '24px',
        'pending-row' => '56px',
        'team-finder' => '80px',
        'lobby-card' => '250px',
        'team-hero' => '(min-width: 1024px) 192px, min(100vw, 176px)',
        'scrim-row' => '60px',
        'join-preview' => '(min-width: 768px) 280px, 100vw',
    ];

    $resolvedSrc = $src ?? $team->logo_url;
    $resolvedSizes = $sizesPresets[$preset] ?? $sizesPresets['thumbnail'];
    $resolvedAlt = $alt ?? 'Logo de l\'équipe '.$team->name;
    $resolvedLoading = $loading ?? ($preset === 'topbar' ? 'eager' : 'lazy');
    $useSrcset = $src === null && filled($team->logo_srcset);
@endphp

<img
    src="{{ $resolvedSrc }}"
    alt="{{ $resolvedAlt }}"
    loading="{{ $resolvedLoading }}"
    @if ($useSrcset)
        srcset="{{ $team->logo_srcset }}"
        sizes="{{ $resolvedSizes }}"
    @endif
    {{ $attributes }}
/>
