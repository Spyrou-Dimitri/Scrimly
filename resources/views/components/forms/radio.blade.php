@props([
    'name',
    'label' => '',
    'options' => [],
    'hasLabel' => true,
    'required' => false,
    'columns' => 4,
])

<div class="flex flex-col gap-2 w-full">
    @if($hasLabel && $label)
        <p class="block font-medium text-white">
            {{ $label }}
            @if($required)
                <span class="text-gold font-bold">*</span>
            @endif
        </p>
    @endif

    <div class="grid gap-6" style="grid-template-columns: repeat({{ $columns }}, minmax(0, 1fr))">
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
            @endphp

            <label
                x-data
                class="relative cursor-pointer"
            >
                <input
                    type="radio"
                    name="{{ $name }}"
                    value="{{ $optionValue }}"
                    class="peer sr-only"
                    {{ $attributes->whereStartsWith('wire:model') }}
                    @if($required) required @endif
                >
                <div class="
                    flex items-center justify-center
                    px-4 py-2 leading-tight
                    text-center font-medium
                    bg-bg-card text-white
                    border border-transparent
                    transition-all duration-200
                    peer-checked:bg-gold peer-checked:text-black
                    peer-focus-visible:ring-1 peer-focus-visible:ring-gold-light
                    hover:opacity-80
                ">
                    {{ $optionLabel }}
                </div>
            </label>
        @endforeach
    </div>

    {{ $slot }}
</div>
