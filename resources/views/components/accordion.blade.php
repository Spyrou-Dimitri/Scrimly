@props([
    'title' => null,
    'open' => false,
    'count' => null,
    'headingLevel' => 'h2',
    'panelTag' => 'ul',
    'panelClass' => 'mt-6 grid grid-cols-12 gap-4 md:gap-6',
])

@php
    $isHeadingH3 = $headingLevel === 'h3';
    $hasHeaderSlot = isset($header) && $header->isNotEmpty();
    $hasActionsSlot = isset($actions) && $actions->isNotEmpty();
@endphp

<section x-data="{ open: @js($open) }" {{ $attributes->merge(['class' => 'p-6 bg-bg-widget basic-shadow flex flex-col']) }}>
    <div class="flex items-center gap-4 justify-between">
        <div class="min-w-0 flex-1">
            @if ($hasHeaderSlot)
                {{ $header }}
            @elseif ($isHeadingH3)
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
        </div>
        <div class="flex shrink-0 items-center gap-4">
            @if ($hasActionsSlot)
                {{ $actions }}
            @endif
            <button
                type="button"
                class="group shrink-0 cursor-pointer"
                x-on:click.prevent="open = !open"
                :aria-expanded="open">
                <flux:icon.chevron-down
                    class="size-8 transition-all duration-150 ease-in-out group-hover:text-gold"
                    ::class="open ? 'rotate-0 text-gold' : '-rotate-90 text-text-gray'" />
            </button>
        </div>
    </div>

    <{{ $panelTag }}
        @class([$panelClass])
        x-show="open"
        @unless($open)
            x-cloak
        @endunless
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2">

        {{ $slot }}
    </{{ $panelTag }}>
</section>
