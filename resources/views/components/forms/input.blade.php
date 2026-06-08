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
    'srOnlyLabel' => false,
    'score' => false,
])

@php
    $baseInputClass = 'box-border min-h-11 bg-input-bg border-1 border-input-border py-2 text-base text-white w-full outline-none focus:ring-2 focus:ring-gold transition-all duration-150';
    $inputType = $score ? 'number' : $type;
    $paddingClass = $inputType === 'search' ? 'pl-10 pr-4' : 'px-4';
    $inputClass = $baseInputClass.' '.$paddingClass;
    $labelClass = $srOnlyLabel ? 'sr-only' : 'block text-white font-medium';
    $hasError = $errors->has($name);
    $errorId = $name.'-error';
    $ariaAttributes = $hasError
        ? ['aria-invalid' => 'true', 'aria-describedby' => $errorId]
        : [];
@endphp

<div class="flex flex-col gap-2 w-full">
    <label
        for="{{ $name }}"
        class="{{ $labelClass }}">
        {{ $label }}
        @if($required)
            <span class="text-gold">
                *
            </span>
        @endif
    </label>

    @if($inputType === 'search')
        <div class="relative w-full">
            <flux:icon
                name="magnifying-glass"
                class="pointer-events-none absolute left-3 top-1/2 size-5 -translate-y-1/2 text-text-secondary"
            />
    @endif

    <input
        {{ $attributes->whereStartsWith('wire:model')}}
        @if($multiple) multiple @endif
        type="{{ $inputType }}"
        id="{{ $name }}"
        name="{{ $name }}"
        placeholder="{{ $placeholder ?? '' }}"
        @if($required)
            required
        @endif
        @if($score)
            min="0"
            step="1"
            autocomplete="off"
            x-on:keydown="
                if ($event.ctrlKey || $event.metaKey || $event.altKey) return;
                if (['Backspace', 'Delete', 'Tab', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Home', 'End'].includes($event.key)) return;
                if ( !/\d/.test($event.key)) $event.preventDefault();
            "
        @endif
        value="{{ old($name) ?? $value }}"
        @foreach($ariaAttributes as $attr => $val) {{ $attr }}="{{ $val }}" @endforeach
        {{ $attributes->merge(['class' => $inputClass]) }}
    >

    @if($inputType === 'search')
        </div>
    @endif

    @error($name)
        <span id="{{ $errorId }}" role="alert" class="font-spaceGrotesk text-input-error font-semibold">
            {{ $message }}
        </span>
    @enderror

    {{$slot}}

</div>
