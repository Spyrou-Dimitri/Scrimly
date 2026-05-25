@php
    use App\Enums\TypeEvents;

    $scrimColor = '#D4AF37';
@endphp

<aside class="bg-bg-widget p-6 shadow-basic">
    <h3 class="mb-4 text-xl font-bold text-gold">
        {{ __('pages/calendar/index.legend_title') }}
    </h3>

    <ul class="flex flex-wrap gap-x-8 gap-y-3">
        <li class="flex items-center gap-2.5">
            <span
                class="size-2.5 shrink-0 rounded-full"
                style="background-color: {{ $scrimColor }}"
                aria-hidden="true"></span>
            <span class="text-sm font-medium text-white">
                {{ __('pages/calendar/index.legend_scrim') }}
            </span>
        </li>

        @foreach (TypeEvents::cases() as $type)
            <li class="flex items-center gap-2.5">
                <span
                    class="size-2.5 shrink-0 rounded-full"
                    style="background-color: {{ $type->color() }}"
                    aria-hidden="true"></span>
                <span class="text-sm font-medium text-white">
                    {{ $type->label() }}
                </span>
            </li>
        @endforeach
    </ul>

    <div class="mt-5 border-t border-white/10 pt-5">
        <p class="mb-3 text-sm font-medium text-text-secondary">
            {{ __('pages/calendar/index.legend_formats_title') }}
        </p>
        <ul class="flex flex-wrap gap-x-8 gap-y-3">
            <li class="flex items-center gap-2.5">
                <span
                    class="h-3 w-8 shrink-0 rounded-sm bg-gold"
                    aria-hidden="true"></span>
                <span class="text-sm font-medium text-white">
                    {{ __('pages/calendar/index.legend_all_day') }}
                </span>
            </li>
            <li class="flex items-center gap-2.5">
                <span
                    class="size-2.5 shrink-0 rounded-full bg-gold"
                    aria-hidden="true"></span>
                <span class="text-sm font-medium text-white">
                    {{ __('pages/calendar/index.legend_time_slot') }}
                </span>
            </li>
        </ul>
    </div>
</aside>
