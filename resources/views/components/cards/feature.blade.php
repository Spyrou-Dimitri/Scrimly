@props([
    'icon',
    'title',
    'description',
])

<article {{ $attributes->merge(['class' => 'flex shadow-basic flex-col items-center text-white gap-6  bg-bg-widget p-8 text-center text-black']) }}>
    <flux:icon name="{{ $icon }}" class="size-12 shrink-0 text-gold" aria-hidden="true" />

    <h3 class="welcome-heading-card text-white">
        {{ $title }}
    </h3>

    <p class="text-base text-text-secondary leading-relaxed">
        {{ $description }}
    </p>
</article>
