@props(['title'])
<div wire:click="dispatch('close_modal')"
    @keydown.escape.window="$wire.dispatch('close_modal')"
    x-trap.inert.noscroll="true"
    class="fixed flex justify-center items-center w-full min-h-screen top-0 z-60 right-0 bg-black/80">
    <section class="w-full bg-bg-widget shadow-modal max-w-md flex flex-col gap-6 py-8 px-6" @click.stop>
        <div class="flex justify-between items-center pb-4 border-b border-gold">
            <h2 class="text-2xl font-bold">{{$title}}</h2>
            <button type="button" wire:click="dispatch('close_modal')"
                class="cursor-pointer w-fit self-end">
                <svg viewBox="0 0 24 24" fill="none" width="28" height=28" xmlns="http://www.w3.org/2000/svg">
                    <g id="SVGRepo_iconCarrier">
                        <path fill-rule="evenodd" clip-rule="evenodd"
                            d="M5.29289 5.29289C5.68342 4.90237 6.31658 4.90237 6.70711 5.29289L12 10.5858L17.2929 5.29289C17.6834 4.90237 18.3166 4.90237 18.7071 5.29289C19.0976 5.68342 19.0976 6.31658 18.7071 6.70711L13.4142 12L18.7071 17.2929C19.0976 17.6834 19.0976 18.3166 18.7071 18.7071C18.3166 19.0976 17.6834 19.0976 17.2929 18.7071L12 13.4142L6.70711 18.7071C6.31658 19.0976 5.68342 19.0976 5.29289 18.7071C4.90237 18.3166 4.90237 17.6834 5.29289 17.2929L10.5858 12L5.29289 6.70711C4.90237 6.31658 4.90237 5.68342 5.29289 5.29289Z"
                            fill="#FFFFFF"></path>
                    </g>
                </svg>
        </div>
        {{$slot}}
    </section>
</div>