@props([
    'label' => '',
    'name' => '',
    'placeholder' => '',
    'rows' => 10,
    'srOnlyLabel' => false,
])

@php
    $labelClass = $srOnlyLabel ? 'sr-only' : 'block font-medium';
@endphp

<div class="flex flex-col gap-2 w-full">
    <label class="{{ $labelClass }}" for="{!! $name !!}">
        {{ $label }}
    </label>
    <textarea
        rows="{{ $rows }}"
        name="{!! $name !!}"
        id="{!! $name !!}"
        placeholder="{!! $placeholder !!}"
        {{ $attributes->merge(['class' => 'py-2 bg-input-bg px-4 border-1 border-input-border w-full outline-none focus:ring-2 focus:ring-gold transition-all duration-200']) }}></textarea>
    {{ $slot }}
</div>
