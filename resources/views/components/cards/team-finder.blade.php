@props([
    'team',
])

@php
    use App\Enums\LolTier;

    $tierPresentation = LolTier::fromNumericValue($team->starter_average_elo);

    $tagStyle = static function (string $cssVar): string {
        return sprintf(
            'color: var(%1$s); background-color: color-mix(in srgb, var(%1$s) 22%%, var(--color-bg-widget));',
            $cssVar
        );
    };
@endphp

@php
    $teamCardHeadingId = 'team-finder-'.$team->id;
@endphp

<article aria-labelledby="{{ $teamCardHeadingId }}" {{ $attributes->merge(['class' => 'relative flex min-h-full flex-col border-l-2 border-gold bg-bg-widget p-4 basic-shadow md:p-5 card-animated-border']) }}>
    <span class="card-animated-border-right-edge" aria-hidden="true"></span>
    <a
        href="{{ route('team.show', ['slug' => currentTeam()->slug, 'id' => $team->id]) }}"
        wire:navigate
        title="{{ __('pages/scrims/find.view_team_card') }} {{ $team->name }}"
        class="absolute inset-0 z-10 rounded-[inherit] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gold"
        aria-label="{{ __('pages/scrims/find.view_team_card', ['name' => $team->name]) }}"
    ></a>
    <div class="relative z-[1] flex min-h-0 flex-1 flex-col gap-4 pointer-events-none">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:gap-4">
            <div class="size-14 shrink-0 overflow-hidden sm:size-20">
                <x-team-logo
                    :team="$team"
                    preset="team-finder"
                    alt=""
                    class="size-full object-cover"
                />
            </div>
            <div class="flex-1">
                <h3 id="{{ $teamCardHeadingId }}" class="truncate text-2xl font-bold text-white">{{ $team->name }}</h3>
                <div class="mt-2 flex flex-wrap gap-2" role="list">
                    <span role="listitem" class="inline-flex items-center text-tag-server bg-tag-server/20 rounded-full px-3 py-2 text-xs font-medium">
                        {{ $team->server->label() }}
                    </span>
                    <span
                        role="listitem"
                        class="inline-flex items-center text-tag-language bg-tag-language/20 rounded-full px-3 py-2 text-xs font-medium">
                        {{ $team->language->label() }}
                    </span>
                    <span
                        role="listitem"
                        class="inline-flex items-center {{ $team->goal->macaron() }} rounded-full px-3 py-2 text-xs font-medium"
                        >
                        {{ $team->goal->label() }}
                    </span>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between gap-4">
            @if ($tierPresentation)
                <div class="flex flex-1 items-center gap-2 font-medium">
                    <img
                        src="{{ asset($tierPresentation['icon']) }}"
                        class="size-8 shrink-0"
                        alt=""
                        aria-hidden="true"
                    >
                    <span class="truncate">{{ $tierPresentation['label'] }}</span>
                </div>
            @else
                <div class="flex flex-1 items-center gap-2 text-sm font-medium text-text-secondary">
                    <span class="truncate">{{ __('pages/scrims/find.unranked') }}</span>
                </div>
            @endif

            <p class="shrink-0 text-sm text-text-secondary">
            {{ $team->members_count }} {{ __('pages/scrims/find.members') }} 
            </p>
        </div>

        @if (filled($team->description))
            <hr class="border-white/10" aria-hidden="true">
            <p class="line-clamp-2 text-sm italic text-text-secondary">
                "{{ $team->description }}"
            </p>
        @endif
    </div>
</article>
