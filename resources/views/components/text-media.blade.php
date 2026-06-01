@props([
    'number',
    'title',
    'description',
    'reverse' => false,
])

<article {{ $attributes->merge(['class' => 'grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-center']) }}>
    <div @class(['flex flex-col gap-4', 'lg:order-2' => $reverse])>
        <div class="flex size-20 items-center justify-center rounded-full bg-gold shrink-0">
            <span class="text-5xl font-bold text-black leading-none">{{ $number }}</span>
        </div>

        <h3 class="text-[32px] font-bold leading-tight">
            {{ $title }}
        </h3>

        <p class="text-base text-text-secondary leading-relaxed">
            {{ $description }}
        </p>
    </div>

    <div  @class(['overflow-hidden shadow-basic bg-white', 'lg:order-1' => $reverse])>
        {{ $media }}
    </div>
</article>
