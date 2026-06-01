@props([
    'icon',
    'title',
    'description',
])

<article {{ $attributes->merge(['class' => 'flex shadow-basic flex-col items-center text-white gap-6 border bg-bg-widget border-black p-8 text-center text-black']) }}>
    <flux:icon name="{{ $icon }}" class="size-12 shrink-0 text-gold" />

    <h3 class="text-2xl text-white font-bold leading-tight">
        {{ $title }}
    </h3>

    <p class="text-base text-text-secondary leading-relaxed">
        {{ $description }}
    </p>
</article>
