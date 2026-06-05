@props([
    'name',
    'label' => '',
    'options' => [],
    'hasLabel' => true,
    'required' => false,
    'columns' => 4,
    'variant' => 'default',
    'gridGapClass' => 'gap-6',
    'fitContent' => false,
    'srOnlyLabel' => false,
])

@php
    $hasError = $errors->has($name);
    $errorId = $name.'-error';
    $legendClass = $srOnlyLabel ? 'sr-only' : 'block font-medium text-white';
@endphp

<div @class([
    'flex flex-col gap-2',
    'w-full' => ! $fitContent,
    'w-fit' => $fitContent,
])>
    @if($hasLabel && $label)
        <p class="{{ $legendClass }}" @if($hasError) id="{{ $errorId }}-legend" @endif>
            {{ $label }}
            @if($required)
                <span class="text-gold font-bold">*</span>
            @endif
        </p>
    @endif

    <div
        @if($hasError) role="group" aria-describedby="{{ $errorId }}" aria-invalid="true" @endif
        @class([
            $gridGapClass,
            'grid' => ! $fitContent,
            'flex flex-wrap' => $fitContent,
        ])
        @unless($fitContent)
            style="grid-template-columns: repeat({{ $columns }}, minmax(0, 1fr))"
        @endunless
    >
        @foreach($options as $option)
            @php
                if (is_object($option)) {
                    if ($option instanceof BackedEnum) {
                        $optionValue = $option->value;
                        $optionLabel = $option->label();
                    } else {
                        $optionValue = $option->id ?? $option->name ?? $option;
                        $optionLabel = $option->name ?? $option;
                    }
                } elseif (is_array($option)) {
                    $optionValue = $option['value'] ?? $option['id'] ?? $option['name'] ?? $option;
                    $optionLabel = $option['label'] ?? $option['name'] ?? $option;
                } else {
                    $optionValue = $option;
                    $optionLabel = $option;
                }

                $checkedStateClasses = match ($variant) {
                    'outcome' => match ($optionValue) {
                        '1' => 'peer-checked:bg-victory peer-checked:text-black peer-focus-visible:ring-2 peer-focus-visible:ring-victory',
                        '0' => 'peer-checked:bg-defeat peer-checked:text-black peer-focus-visible:ring-2 peer-focus-visible:ring-defeat',
                        default => 'peer-checked:bg-gold peer-checked:text-black peer-focus-visible:ring-1 peer-focus-visible:ring-gold-light',
                    },
                    default => 'peer-checked:bg-gold peer-checked:text-black peer-focus-visible:ring-1 peer-focus-visible:ring-gold-light',
                };
            @endphp

            <label
                x-data
                @class([
                    'relative cursor-pointer',
                    'w-fit' => $fitContent,
                ])
            >
                <input
                    type="radio"
                    name="{{ $name }}"
                    value="{{ $optionValue }}"
                    class="peer sr-only"
                    {{ $attributes->whereStartsWith('wire:model') }}
                    @if($required) required @endif
                >
                <div @class([
                    'flex items-center justify-center px-4 py-2 leading-tight text-center font-medium',
                    'bg-bg-card text-white border border-transparent transition-all duration-150',
                    'hover:opacity-80',
                    $checkedStateClasses,
                ])>
                    {{ $optionLabel }}
                </div>
            </label>
        @endforeach
    </div>

    @error($name)
        <span id="{{ $errorId }}" role="alert" class="font-spaceGrotesk text-input-error font-semibold">
            {{ $message }}
        </span>
    @enderror

    {{ $slot }}
</div>
