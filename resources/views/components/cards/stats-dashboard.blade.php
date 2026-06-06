@props([
'title',
'value',
])

@php
    $headingId = 'stats-dashboard-'.md5($title);
@endphp

<div
    aria-labelledby="{{ $headingId }}"
    x-data="{incrementor: 0}"
    x-init="
    let start = null;
    const duration = 1000;
    const target = {{ $value }};
    function animate(timestamp) {
        if (!start) start = timestamp;
        let t = Math.min((timestamp - start) / duration, 1);
        incrementor = Math.round(t * target);
        if (t < 1) requestAnimationFrame(animate);
    } 
    requestAnimationFrame(animate);"
    {{ $attributes->merge(['class' => 'flex w-full flex-col gap-2 bg-bg-widget justify-center p-6 shadow-basic']) }}>
    <p id="{{ $headingId }}" class="text-text-secondary">
        {{ $title }}
    </p>
    <p class="text-[40px] leading-none text-center text-gold font-bold tabular-nums" x-text="incrementor"></p>

</div>