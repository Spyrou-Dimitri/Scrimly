@props([
    'widthFull' => false,
    'title' => '',
    'type' => 'button',
    'onlyIcon' => false,
])

@php
$classes = ['cta-success', 'cta-success--cta', 'cta-success--outline', 'w-full' => $widthFull, 'cta-success--only-icon' => $onlyIcon];
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['title' => $title])->class($classes) }}>
    {{ $slot }}
</button>
