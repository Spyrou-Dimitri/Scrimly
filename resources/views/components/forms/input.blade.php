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

<div class="flex flex-col gap-2">
    <label
        for="{{ $name }}"
        class="{{ $type === 'search' ? 'hidden' : 'block text-white font-medium' }}">
        {{ $label }}
        @if($required)
            <span class="text-gold">
                *
            </span>
        @endif
    </label>

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

        {{ $attributes->merge(['class' => "bg-input-bg border-1 border-input-border py-2 px-4 text-white w-full outline-none focus:ring-2 focus:ring-gold transition-all duration-200"]) }}
        @if($type === 'search')
            wire:model.live.debounce="{{$term}}"
        @endif
    >


    {{$slot}}

</div>