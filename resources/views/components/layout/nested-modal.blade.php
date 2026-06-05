@props(['title', 'width' => 'xl', 'show'])

@php
    $width_variants = [
        'md' => 'w-[90%] md:w-full max-w-md',
        'xl' => 'w-[90%] md:w-full max-w-xl',
        '2xl' => 'w-[90%] md:w-full max-w-2xl',
        '3xl' => 'w-[90%] md:w-full max-w-3xl',
    ];
    $width_variant = $width_variants[$width] ?? $width_variants['xl'];
    $modalTitleId = 'modal-title-'.md5($title);
@endphp

<div
    x-show="{{ $show }}"
    x-cloak
    @keydown.escape.window.stop="{{ $show }} = false"
    @click="{{ $show }} = false"
    x-trap.inert.noscroll="{{ $show }}"
    class="fixed inset-0 z-70 flex items-center justify-center bg-black/80 p-4">
    <section
        class="{{ $width_variant }} max-h-[90vh] bg-bg-main shadow-modal flex flex-col overflow-hidden py-6 px-4 md:py-8 md:px-6"
        @click.stop
        role="dialog"
        aria-modal="true"
        aria-labelledby="{{ $modalTitleId }}">
        <div class="flex shrink-0 items-center justify-between border-b-2 border-gold pb-4 mb-4">
            <h2 id="{{ $modalTitleId }}" class="text-2xl font-bold">{{ $title }}</h2>
            <button
                type="button"
                @click="{{ $show }} = false"
                class="w-fit cursor-pointer self-end"
                aria-label="{{ __('layouts/modal.close') }}"
                title="{{ __('layouts/modal.close') }}">
                <svg viewBox="0 0 24 24" fill="none" width="28" height="28" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M5.29289 5.29289C5.68342 4.90237 6.31658 4.90237 6.70711 5.29289L12 10.5858L17.2929 5.29289C17.6834 4.90237 18.3166 4.90237 18.7071 5.29289C19.0976 5.68342 19.0976 6.31658 18.7071 6.70711L13.4142 12L18.7071 17.2929C19.0976 17.6834 19.0976 18.3166 18.7071 18.7071C18.3166 19.0976 17.6834 19.0976 17.2929 18.7071L12 13.4142L6.70711 18.7071C6.31658 19.0976 5.68342 19.0976 5.29289 18.7071C4.90237 18.3166 4.90237 17.6834 5.29289 17.2929L10.5858 12L5.29289 6.70711C4.90237 6.31658 4.90237 5.68342 5.29289 5.29289Z"
                        fill="#FFFFFF"></path>
                </svg>
            </button>
        </div>
        <div class="min-h-0 flex-1 overflow-y-auto p-0.5">
            {{ $slot }}
        </div>
    </section>
</div>
