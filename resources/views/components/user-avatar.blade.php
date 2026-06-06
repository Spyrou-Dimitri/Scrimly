@props([
    'user',
    'preset' => 'thumbnail',
    'alt' => null,
    'loading' => null,
    'src' => null,
])

@php
    $sizesPresets = [
        'thumbnail' => '40px',
        'team-row' => '60px',
        'topbar' => '36px',
        'roster-candidate' => '(max-width: 1279px) 80px, 96px',
        'player-card' => '(min-width: 768px) 33vw, 100vw',
        'profile-hero' => '(min-width: 1024px) 25vw, min(100vw, 280px)',
        'modal-preview' => '(min-width: 768px) 80px, 100vw',
    ];

    $resolvedSrc = $src ?? $user->avatar_url;
    $resolvedSizes = $sizesPresets[$preset] ?? $sizesPresets['thumbnail'];
    $resolvedAlt = $alt ?? 'Photo de profil de '.$user->username;
    $resolvedLoading = $loading ?? ($preset === 'topbar' ? 'eager' : 'lazy');
    $useSrcset = $src === null && filled($user->avatar_srcset);
@endphp

<img
    src="{{ $resolvedSrc }}"
    alt="{{ $resolvedAlt }}"
    loading="{{ $resolvedLoading }}"
    @if ($useSrcset)
        srcset="{{ $user->avatar_srcset }}"
        sizes="{{ $resolvedSizes }}"
    @endif
    {{ $attributes }}
>
