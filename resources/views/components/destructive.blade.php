@props([
    'widthFull' => false,
    'title' => '',
    'type' => 'button',
])

@php
$classes = ['cta-danger', 'cta-danger--cta', 'cta-danger--outline', 'w-full' => $widthFull];
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['title' => $title])->class($classes) }}>
    {{ $slot }}
</button>
