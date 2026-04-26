@props([
    'name',
    'label' => '',
    'options' => [],
    'hasLabel' => true,
    'multiple' => false,
    'required' => false,
    'disabled' => null,
    'new_instance' => false,
    'new_instance_value' => false,
    'new_instance_label' => false,
])

<div class="flex flex-col gap-2 w-full">
    @if($hasLabel)
        <label for="{{ $name }}" class="block font-medium">
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
        class="bg-input-bg border-input-border border-1 py-2 px-4 w-full focus:outline-none focus:ring-1 focus:ring-gold-light"
        {{$attributes->whereStartsWith('wire:model')}}
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
        {{$slot}}

</div>