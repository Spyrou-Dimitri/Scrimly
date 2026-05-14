@props([
    'widthFull' => false,
    'title' => '',
    'type' => 'button',
    'onlyIcon' => false,
])

@php
$classes = ['cta-danger', 'cta-danger--cta', 'cta-danger--outline', 'w-full' => $widthFull, 'cta-danger--only-icon' => $onlyIcon];
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['title' => $title])->class($classes) }}>
    {{ $slot }}
</button>
