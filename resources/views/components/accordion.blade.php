@props([
    'title',
    'open' => false,
    'count' => null,
    'headingLevel' => 'h2',
])

@php
    $isHeadingH3 = $headingLevel === 'h3';
@endphp

<section x-data="{ open: @js($open) }" {{ $attributes->merge(['class' => 'p-6 bg-bg-widget basic-shadow flex flex-col']) }}>
    <div @class([
        'flex items-center gap-4 justify-between',
        'border-b border-gold-border pb-4' => $isHeadingH3,
    ])>
        @if ($isHeadingH3)
            <h3 class="text-[24px] font-bold text-gold">
                {{ $title }}
                @if ($count)
                    <span class="font-bold">({{ $count }})</span>
                @endif
            </h3>
        @else
            <h2 class="text-[32px] font-bold">
                {{ $title }}
                @if ($count)
                    <span class="text-gold font-bold">({{ $count }})</span>
                @endif
            </h2>
        @endif
        <button type="button" class="group cursor-pointer" x-on:click.prevent="open = !open">
            <flux:icon.chevron-down
                class="size-8 transition-all duration-150 ease-in-out text-text-gray group-hover:text-gold"
                ::class="open ? 'rotate-0 text-gold' : '-rotate-90 text-text-gray'" />
        </button>
    </div>
    <ul class="mt-6 grid grid-cols-12 gap-4 md:gap-6"
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2">

        {{ $slot }}
    </ul>
</section>
