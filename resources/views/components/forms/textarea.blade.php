<div class="flex flex-col gap-2 w-full">
    <label class="block font-medium" for="{!! $name !!}">
        {{ $label }}
    </label>
    <textarea
        rows="10"
        name="{!! $name !!}"
        id="{!! $name !!}"
        placeholder="{!! $placeholder !!}"
        {{ $attributes->merge(['class' => 'py-2 bg-input-bg px-4 border-1 border-input-border w-full outline-none focus:ring-2 focus:ring-gold transition-all duration-200']) }}></textarea>
    {{ $slot }}
</div>
