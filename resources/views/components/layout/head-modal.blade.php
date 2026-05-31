@props(['title', 'width' => 'md', 'height' => 'auto', 'destroy' => false])
@php
    $width_variants = [
        'md' => 'w-[90%] md:w-full max-w-md',
        'xl' => 'w-[90%] md:w-full max-w-xl',
        '2xl' => 'w-[90%] md:w-full max-w-2xl',
        '3xl' => 'w-[90%] md:w-full max-w-3xl',
        '5xl' => 'w-[95%] lg:w-[90%] xl:w-full max-w-7xl',
        ];
    $height_variants = [
        'auto' => 'max-h-[90vh]',
        '75' => 'max-h-[75vh]',
    ];
    $destroy_variants = [
        true => 'border-b-2 border-red-900',
        false => 'border-b-2 border-gold',
    ];
    $destroy_variant = $destroy_variants[filter_var($destroy, FILTER_VALIDATE_BOOLEAN)];
    $width_variant = $width_variants[$width] ?? $width_variants['md'];
    $height_variant = $height_variants[$height] ?? $height_variants['auto'];
@endphp
<div wire:click="dispatch('close_modal')"
    @keydown.escape.window="$wire.dispatch('close_modal')"
    x-trap.inert.noscroll="true"
    class="fixed flex justify-center items-center w-full min-h-screen top-0 z-60 right-0 bg-black/80">
    <section class="{{ $width_variant }} {{ $height_variant }} bg-bg-main shadow-modal flex flex-col overflow-hidden py-6 px-4 md:py-8 md:px-6" @click.stop>
        <div class="flex shrink-0 justify-between items-center pb-4 mb-6 {{ $destroy_variant }}">
            <h2 class="text-2xl font-bold">{{ $title }}</h2>
            <button type="button" wire:click="dispatch('close_modal')"
                class="cursor-pointer hover:text-gold transition-colors duration-150 w-fit self-end">
                <svg viewBox="0 0 24 24" fill="none" width="28" height="28" xmlns="http://www.w3.org/2000/svg">
                    <g id="SVGRepo_iconCarrier">
                        <path fill-rule="evenodd" clip-rule="evenodd"
                            d="M5.29289 5.29289C5.68342 4.90237 6.31658 4.90237 6.70711 5.29289L12 10.5858L17.2929 5.29289C17.6834 4.90237 18.3166 4.90237 18.7071 5.29289C19.0976 5.68342 19.0976 6.31658 18.7071 6.70711L13.4142 12L18.7071 17.2929C19.0976 17.6834 19.0976 18.3166 18.7071 18.7071C18.3166 19.0976 17.6834 19.0976 17.2929 18.7071L12 13.4142L6.70711 18.7071C6.31658 19.0976 5.68342 19.0976 5.29289 18.7071C4.90237 18.3166 4.90237 17.6834 5.29289 17.2929L10.5858 12L5.29289 6.70711C4.90237 6.31658 4.90237 5.68342 5.29289 5.29289Z"
                            fill="currentColor"></path>
                    </g>
                </svg>
            </button>
        </div>
        <div class="min-h-0 flex-1 overflow-y-auto p-0.5">
            {{ $slot }}
        </div>
    </section>
</div>