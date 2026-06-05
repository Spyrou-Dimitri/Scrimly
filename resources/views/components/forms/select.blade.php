@props([
    'name',
    'label' => '',
    'options' => [],
    'hasLabel' => true,
    'multiple' => false,
    'required' => false,
    'disabled' => null,
    'inputDisabled' => false,
    'new_instance' => false,
    'new_instance_value' => false,
    'new_instance_label' => false,
    'labelNextToSelect' => false,
    'srOnlyLabel' => false,
])

@php
    $hasError = $errors->has($name);
    $errorId = $name.'-error';
    $labelClass = $srOnlyLabel ? 'sr-only' : 'block font-medium';
@endphp

<div @class([ 'flex flex-col gap-2 w-full', 'sm:flex-row sm:items-center' => $labelNextToSelect ])>
    @if($hasLabel)
        <label for="{{ $name }}" class="{{ $labelClass }}">
            {{ $label }}
            @if($required)
                <span class="text-gold font-bold">
                *
            </span>
            @endif
        </label>
    @endif
    <select
        @if($required)
            required
        @endif
        name="{{ $name }}"
        @if($multiple)
            multiple
        @endif
        id="{{ $name }}"
        @if($hasError) aria-invalid="true" aria-describedby="{{ $errorId }}" @endif
        @disabled($inputDisabled)
        @class([
            'box-border min-h-11 border-1 py-2 px-4 w-full border text-base text-white leading-normal',
            'bg-input-bg border-input-border focus:outline-none focus:ring-1 focus:ring-gold-light' => ! $inputDisabled,
            'cursor-not-allowed border-white/10 bg-black/40 text-text-secondary opacity-70 shadow-none focus:ring-0' => $inputDisabled,
            'min-w-[10rem]' => $labelNextToSelect,
        ])
        {{ $attributes->whereStartsWith('wire:model') }}
    >
    
        @if($disabled)
        <option selected
                value="">{{$disabled}}
        </option>
        @endif

        @if($new_instance)
            <option value="{{$new_instance_value}}">{{$new_instance_label}}</option>
        @endif

        @foreach($options as $key => $option)
            @php
                if (is_object($option)) {
                    if ($option instanceof BackedEnum) {
                        $optionValue = $option->value;
                        $optionLabel = $option->label();
                    }
                    else {
                        $optionValue = $option->id ?? $option->name ?? $option;
                        $optionLabel = $option->name ?? $option;
                    }
                } elseif (is_array($option)) {
                    $optionValue = $option['id'] ?? $option['name'] ?? $option;
                    $optionLabel = $option['name'] ?? $option;
                } else {
                    $optionValue = $option;
                    $optionLabel = $option;
                }
            @endphp
            <option value="{{ $optionValue }}">
                {{ $optionLabel }}
            </option>
        @endforeach
    </select>

    @error($name)
        <span id="{{ $errorId }}" role="alert" class="font-spaceGrotesk text-input-error font-semibold">
            {{ $message }}
        </span>
    @enderror

    {{$slot}}

</div>