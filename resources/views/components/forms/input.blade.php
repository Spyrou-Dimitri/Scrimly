@props([
    'name',
    'type',
    'label',
    'placeholder',
    'value' => '',
    'message' => '',
    'required' => false,
    'multiple' => false,
    'term' => false,
])

@php
    $baseInputClass = 'box-border min-h-11 bg-input-bg border-1 border-input-border py-2 text-base text-white leading-normal w-full outline-none focus:ring-2 focus:ring-gold transition-all duration-200';
    $paddingClass = $type === 'search' ? 'pl-10 pr-4' : 'px-4';
    $inputClass = $baseInputClass.' '.$paddingClass;
@endphp

<div class="flex flex-col gap-2 w-full">
    <label
        for="{{ $name }}"
        class="block text-white font-medium">
        {{ $label }}
        @if($required)
            <span class="text-gold">
                *
            </span>
        @endif
    </label>

    @if($type === 'search')
        <div class="relative w-full">
            <flux:icon
                name="magnifying-glass"
                class="pointer-events-none absolute left-3 top-1/2 size-5 -translate-y-1/2 text-text-secondary"
            />
            <input
                {{ $attributes->whereStartsWith('wire:model')}}
                @if($multiple) multiple @endif
                type="{{ $type }}"
                id="{{ $name }}"
                name="{{ $name }}"
                placeholder="{{ $placeholder ?? '' }}"
                @if($required)
                    required
                @endif
                value="{{ old($name) ?? $value }}"

                {{ $attributes->merge(['class' => $inputClass]) }}
            >
        </div>
    @else
        <input
            {{ $attributes->whereStartsWith('wire:model')}}
            @if($multiple) multiple @endif
            type="{{ $type }}"
            id="{{ $name }}"
            name="{{ $name }}"
            placeholder="{{ $placeholder ?? '' }}"
            @if($required)
                required
            @endif
            value="{{ old($name) ?? $value }}"

            {{ $attributes->merge(['class' => $inputClass]) }}
        >
    @endif

    {{$slot}}

</div>
