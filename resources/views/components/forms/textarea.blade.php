@props([
    'label' => '',
    'name' => '',
    'placeholder' => '',
    'rows' => 10,
    'srOnlyLabel' => false,
    'required' => false,
])

@php
    $labelClass = $srOnlyLabel ? 'sr-only' : 'block font-medium';
    $hasError = $errors->has($name);
    $errorId = $name.'-error';
@endphp

<div class="flex flex-col gap-2 w-full">
    <label class="{{ $labelClass }}" for="{!! $name !!}">
        {{ $label }}
        @if($required)
            <span class="text-gold">*</span>
        @endif
    </label>
    <textarea
        rows="{{ $rows }}"
        name="{!! $name !!}"
        id="{!! $name !!}"
        placeholder="{!! $placeholder !!}"
        @if($required) required @endif
        @if($hasError) aria-invalid="true" aria-describedby="{{ $errorId }}" @endif
        {{ $attributes->merge(['class' => 'py-2 bg-input-bg px-4 border-1 border-input-border w-full outline-none focus:ring-2 focus:ring-gold transition-all duration-200']) }}></textarea>

    @error($name)
        <span id="{{ $errorId }}" role="alert" class="font-spaceGrotesk text-input-error font-semibold">
            {{ $message }}
        </span>
    @enderror

    {{ $slot }}
</div>
