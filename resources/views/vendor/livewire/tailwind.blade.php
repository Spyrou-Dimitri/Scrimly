@php
if (! isset($scrollTo)) {
    $scrollTo = 'body';
}

$scrollIntoViewJsSnippet = ($scrollTo !== false)
    ? <<<JS
       (\$el.closest('{$scrollTo}') || document.querySelector('{$scrollTo}')).scrollIntoView()
    JS
    : '';

$btnNav = 'inline-flex cursor-pointer items-center justify-center px-2.5 py-2 text-sm font-medium transition-colors duration-150 focus:z-10 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold focus-visible:ring-offset-2 focus-visible:ring-offset-bg-main disabled:pointer-events-none';
$btnPage = 'inline-flex min-w-9 cursor-pointer items-center justify-center px-3 py-2 text-sm font-medium transition-colors duration-150 focus:z-10 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold focus-visible:ring-offset-2 focus-visible:ring-offset-bg-main';
$btnMobile = 'relative inline-flex cursor-pointer items-center border px-4 py-2 text-sm font-medium leading-5 transition-colors duration-150 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold focus-visible:ring-offset-2 focus-visible:ring-offset-bg-main';
$disabledNav = 'cursor-pointer bg-bg-card text-text-secondary/45 border-input-border';
$activeNav = 'cursor-pointer bg-bg-card text-text-primary border-input-border hover:bg-bg-widget hover:border-gold hover:text-gold';
$disabledMobile = 'cursor-pointer border-input-border bg-bg-card text-text-secondary/45';
$activeMobile = 'cursor-pointer border-input-border bg-bg-card text-text-primary hover:border-gold hover:text-gold';
@endphp

<div>
    @if ($paginator->hasPages())
        <nav role="navigation" aria-label="Pagination Navigation" class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-1 justify-between sm:hidden">
                <span>
                    @if ($paginator->onFirstPage())
                        <span class="{{ $btnMobile }} {{ $disabledMobile }}">
                            {!! __('pagination.previous') !!}
                        </span>
                    @else
                        <button type="button" wire:click="previousPage('{{ $paginator->getPageName() }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}" wire:loading.attr="disabled" dusk="previousPage{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}.before" class="{{ $btnMobile }} {{ $activeMobile }}">
                            {!! __('pagination.previous') !!}
                        </button>
                    @endif
                </span>

                <span>
                    @if ($paginator->hasMorePages())
                        <button type="button" wire:click="nextPage('{{ $paginator->getPageName() }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}" wire:loading.attr="disabled" dusk="nextPage{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}.before" class="{{ $btnMobile }} ml-3 {{ $activeMobile }}">
                            {!! __('pagination.next') !!}
                        </button>
                    @else
                        <span class="{{ $btnMobile }} ml-3 {{ $disabledMobile }}">
                            {!! __('pagination.next') !!}
                        </span>
                    @endif
                </span>
            </div>

            <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm leading-5 text-text-secondary">
                        <span>{!! __('Showing') !!}</span>
                        <span class="font-semibold text-text-primary">{{ $paginator->firstItem() }}</span>
                        <span>{!! __('to') !!}</span>
                        <span class="font-semibold text-text-primary">{{ $paginator->lastItem() }}</span>
                        <span>{!! __('of') !!}</span>
                        <span class="font-semibold text-text-primary">{{ $paginator->total() }}</span>
                        <span>{!! __('results') !!}</span>
                    </p>
                </div>

                <div>
                    <span class="relative z-0 inline-flex items-stretch gap-2 rtl:flex-row-reverse">
                        {{-- Previous (séparé des numéros) --}}
                        <span class="inline-flex overflow-hidden shadow-basic ring-1 ring-input-border bg-bg-card">
                            @if ($paginator->onFirstPage())
                                <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                                    <span class="{{ $btnNav }} {{ $disabledNav }}" aria-hidden="true">
                                        <svg class="size-5 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                </span>
                            @else
                                <button type="button" wire:click="previousPage('{{ $paginator->getPageName() }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}" dusk="previousPage{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}.after" class="{{ $btnNav }} {{ $activeNav }}" aria-label="{{ __('pagination.previous') }}">
                                    <svg class="size-5 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            @endif
                        </span>

                        {{-- Numéros collés entre eux --}}
                        <span class="relative z-0 inline-flex overflow-hidden shadow-basic ring-1 ring-input-border divide-x divide-input-border bg-bg-card">
                            @foreach ($elements as $element)
                                {{-- "Three Dots" Separator --}}
                                @if (is_string($element))
                                    <span aria-disabled="true">
                                        <span class="relative inline-flex cursor-pointer items-center bg-bg-card px-4 py-2 text-sm font-medium text-text-secondary">{{ $element }}</span>
                                    </span>
                                @endif

                                {{-- Array Of Links --}}
                                @if (is_array($element))
                                    @foreach ($element as $page => $url)
                                        <span wire:key="paginator-{{ $paginator->getPageName() }}-page{{ $page }}">
                                            @if ($page == $paginator->currentPage())
                                                <span aria-current="page">
                                                    <span class="{{ $btnPage }} bg-gold font-semibold text-gold-contrast">{{ $page }}</span>
                                                </span>
                                            @else
                                                <button type="button" wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}" class="{{ $btnPage }} bg-bg-card text-text-gray hover:bg-bg-widget hover:text-gold" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                                    {{ $page }}
                                                </button>
                                            @endif
                                        </span>
                                    @endforeach
                                @endif
                            @endforeach
                        </span>

                        {{-- Next (séparé des numéros) --}}
                        <span class="inline-flex overflow-hidden shadow-basic ring-1 ring-input-border bg-bg-card">
                            @if ($paginator->hasMorePages())
                                <button type="button" wire:click="nextPage('{{ $paginator->getPageName() }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}" dusk="nextPage{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}.after" class="{{ $btnNav }} {{ $activeNav }}" aria-label="{{ __('pagination.next') }}">
                                    <svg class="size-5 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            @else
                                <span aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                                    <span class="{{ $btnNav }} {{ $disabledNav }}" aria-hidden="true">
                                        <svg class="size-5 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                </span>
                            @endif
                        </span>
                    </span>
                </div>
            </div>
        </nav>
    @endif
</div>
