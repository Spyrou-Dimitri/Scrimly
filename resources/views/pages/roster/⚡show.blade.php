<?php

use App\Models\TeamMember;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::team')] class extends Component
{
    public TeamMember $teamMember;

    public function mount(string $slug, int|string $id): void
    {
        $team = currentTeam();

        if (! $team) {
            abort(403);
        }

        $member = TeamMember::query()
            ->whereKey($id)
            ->where('team_id', $team->id)
            ->with([
                'team',
                'user.riotProfile.riotMatches' => fn($query) => $query->orderByDesc('played_at'),
            ])
            ->firstOrFail();

        if ($member->team->slug !== $slug) {
            abort(404);
        }

        $this->teamMember = $member;
    }
};
?>

@php
$user = $teamMember->user;
$riot = $user->riotProfile;
$recentChampions = $riot
? $riot->riotMatches->pluck('champion_name')->unique()->take(3)->toArray()
: [];
$ddragonVersion = config('riot.ddragon_version');
$tierLine = null;
if ($user->tier) {
$tier = $user->tier;
$tierLine = $tier->label();
if ($tier->isApex() && $user->lp !== null && $user->lp !== '') {
$tierLine .= ' • '.$user->lp.' LP';
} elseif (filled($user->rank)) {
$tierLine .= ' • '.$user->rank;
}
}
$winrate = $riot !== null ? $riot->getWinratePercentage() : 0;
$kda = $riot !== null ? $riot->getGeneralKda() : 0.0;
$activeTab = request()->query('tab', 'matches');
$tabHref = fn (string $tab): string => route('roster.show', ['slug' => $teamMember->team->slug, 'id' => $teamMember->id]).'?'.http_build_query(['tab' => $tab]);
$tierHeadingIconClass = 'size-10 shrink-0 object-contain';
@endphp

<div class="flex w-full flex-col gap-8">
    <div class="grid w-full grid-cols-1 gap-4 lg:grid-cols-12 lg:gap-6">
        <div class="mx-auto w-full max-w-[280px] lg:col-span-3 lg:mx-0 lg:max-w-none">
            @if ($user->avatar)
            <img
                src="{{ Storage::disk('public')->url('images/avatar/variants/480x480/'.$user->avatar) }}"
                alt="Photo de profil de {{ $user->username }}"
                class="aspect-square w-full object-cover">
            @else
            <img
                src="{{ asset('/img/basicIcon.webp') }}"
                alt="Photo de profil de {{ $user->username }}"
                class="aspect-square w-full object-cover">
            @endif
        </div>
        <div class="flex w-full flex-col items-center gap-4 md:gap-6 text-center lg:col-span-9 lg:col-start-4 lg:items-stretch lg:justify-center lg:text-left">
            <div class="flex min-w-0 flex-col items-center lg:items-start">
                <div class="flex justify-center gap-2 sm:gap-4 flex-wrap">
                    <h2 class="font-spaceGrotesk text-3xl font-bold text-gold md:text-4xl">{{ $user->username }}</h2>
                    <div class="flex shrink-0 items-center gap-2">
                        @if ($user->tier)
                        <img
                            src="{{ asset($user->tier->icon()) }}"
                            alt=""
                            class="{{ $tierHeadingIconClass }}">
                        @endif
                        @if ($tierLine !== null)
                        <p class="text-xl font-medium text-white">{{ $tierLine }}</p>
                        @else
                        <p class="text-xl text-text-gray">{{ __('pages/roster/show.rank_unknown') }}</p>
                        @endif
                    </div>
                </div>
                @if (filled($user->riot_tag))
                <p class="text-base text-text-gray">{{ $user->riot_tag }}</p>
                @endif
            </div>
            <div class="w-full grid grid-cols-1 gap-4 md:grid-cols-9  lg:gap-8">
                <div class="flex flex-row flex-wrap md:flex-col gap-2 md:col-span-3">
                    <div class="flex-1 flex flex-col gap-1">
                        <span class="text-base text-white">{{ __('pages/roster/show.role_in_game_label') }}</span>
                        <span class="text-xl font-semibold text-gold">{{ $teamMember->roleInGame->label() }}</span>
                    </div>
                    <div class="flex-1 flex flex-col gap-1">
                        <span class="text-base text-white">{{ __('pages/roster/show.kda_average_label') }}</span>
                        <span class="text-xl font-semibold text-gold">{{ number_format($kda, 2, ',', ' ') }}</span>
                    </div>
                </div>

                <div class="flex flex-row flex-wrap gap-2 md:flex-col md:col-span-3">
                    <div class="flex-1 flex flex-col gap-1">
                        <span class="text-base text-white">{{ __('pages/roster/show.role_in_team_label') }}</span>
                        <span class="text-xl font-semibold text-gold">{{ $teamMember->roleInTeam->label() }}</span>
                    </div>
                    <div class="flex-1 flex flex-col gap-1">
                        <span class="text-base text-white">{{ __('pages/roster/show.winrate_label') }}</span>
                        <span class="text-xl font-semibold text-gold">{{ $winrate }}%</span>
                        <div class="h-2 w-full lg:w-1/2 overflow-hidden bg-bg-card">
                            <div
                                class="h-full bg-gold transition-all"
                                style="width: {{ min(100, max(0, $winrate)) }}%"></div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-row flex-wrap gap-2 md:flex-col md:col-span-3">
                    <div class="flex-1 flex flex-col gap-1">
                        <span class="text-base text-white">{{ __('pages/roster/show.member_since_label') }}</span>
                        <span class="text-xl font-semibold text-gold">
                            {{ $teamMember->joined_at?->format('d/m/Y') ?? __('pages/roster/show.member_since_unknown') }}
                        </span>
                    </div>
                    <div class="flex-1 flex flex-col gap-2">
                        <span class="text-base text-white">{{ __('pages/roster/show.recent_champions_label') }}</span>
                        <div class="flex items-center justify-center md:justify-start gap-2" aria-hidden="true">
                            @foreach ($recentChampions as $champion)
                            <img src="https://ddragon.leagueoflegends.com/cdn/{{ $ddragonVersion }}/img/champion/{{ $champion }}.png" alt="Champion" class="aspect-square w-12 shrink-0 rounded border border-[#2C2D34] bg-bg-card/60">
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <nav class="border-b border-[#2C2D34]" aria-label="{{ __('pages/roster/show.tabs_nav_label') }}">
        <h2 class="sr-only">
            {{ __('pages/roster/show.tabs.title') }}
        </h2>
        <ul class="flex flex-wrap gap-6 md:gap-10">
            <li>
                <a
                    wire:navigate
                    href="{{ $tabHref('matches') }}"
                    @class([ 'nav-link inline-block pb-3 text-base font-semibold' , 'nav-link-active'=> $activeTab === 'matches',
                    ])
                    >{{ __('pages/roster/show.tabs.matches') }}</a>
            </li>
            <li>
                <a
                    wire:navigate
                    href="{{ $tabHref('homework') }}"
                    @class([ 'nav-link inline-block pb-3 text-base font-semibold' , 'nav-link-active'=> $activeTab === 'homework',
                    ])
                    >{{ __('pages/roster/show.tabs.homework') }}</a>
            </li>
            <li>
                <a
                    wire:navigate
                    href="{{ $tabHref('availability') }}"
                    @class([ 'nav-link inline-block pb-3 text-base font-semibold' , 'nav-link-active'=> $activeTab === 'availability',
                    ])
                    >{{ __('pages/roster/show.tabs.availability') }}</a>
            </li>

        </ul>
    </nav>

    @if ($activeTab === 'matches')
    <section class="flex flex-col gap-4" aria-labelledby="roster-matches-heading">
        <h3 id="roster-matches-heading" class="font-spaceGrotesk text-2xl font-bold text-white">
            {{ __('pages/roster/show.matches.section_title') }}
        </h3>

        @if ($riot === null)
        <p class="text-base text-text-gray">{{ __('pages/roster/show.matches.no_riot_profile') }}</p>
        @elseif ($riot->riotMatches->isEmpty())
        <p class="text-base text-text-gray">{{ __('pages/roster/show.matches.empty') }}</p>
        @else
        <ul class="flex flex-col gap-4" role="list">
            @foreach ($riot->riotMatches as $match)
            @php
            $duration = $match->getDurationGameMinutes();
            $itemIds = array_values($match->items ?? []);
            $itemIds = array_pad($itemIds, 7, 0);
            $itemIds = array_slice($itemIds, 0, 7);
            @endphp
            <li
                class="flex shadow-basic bg-bg-widget"
                wire:key="match-{{ $match->id }}">

                <div
                    class="flex flex-1 flex-wrap items-center gap-3 p-3 sm:gap-4 sm:p-6">
                    <div class="flex shrink-0 items-stretch gap-4">
                        <div class="w-1 {{ $match->win ? 'bg-victory' : 'bg-defeat' }}"></div>
                        <div class="relative">
                            <img
                                src="https://ddragon.leagueoflegends.com/cdn/{{ $ddragonVersion }}/img/champion/{{ $match->champion_name }}.png"
                                alt="{{ $match->champion_name }}"
                                class="aspect-square w-20 border border-[#2C2D34] bg-bg-card/60">
                            <span class="{{ $match->win ? 'bg-victory' : 'bg-defeat' }} absolute right-0 bottom-0 translate-x-0.5 translate-y-0.5 px-1 py-px text-[10px] font-bold leading-none text-black sm:text-xs">
                                {{ __('pages/roster/show.matches.level_badge', ['level' => $match->champion_level]) }}
                            </span>
                        </div>
                    </div>

                    <div class="min-w-0 flex-1 basis-[8rem] sm:basis-auto">
                        <p class="truncate font-semibold text-white">{{ $match->champion_name }}</p>
                        <p
                            class="text-sm font-medium {{ $match->win ? 'text-emerald-400' : 'text-red-400' }}">
                            {{ $match->win ? __('pages/roster/show.matches.win') : __('pages/roster/show.matches.loss') }}
                        </p>
                    </div>

                    <div class="flex flex-1 basis-[7rem] flex-col sm:basis-auto">
                        <p class="font-semibold text-white">
                            {{ $match->kills }} / {{ $match->deaths }} / {{ $match->assists }}
                        </p>
                        <p class="text-[12px] text-text-gray">
                            {{ __('pages/roster/show.matches.kda_ratio_label') }}
                            <span class="text-white font-bold">{{ $match->getKda() }}</span>
                        </p>
                    </div>

                    <div class="flex flex-1 basis-[5rem] flex-col sm:basis-auto">
                        <p class="font-semibold text-white">
                            {{ $match->cs }} {{ __('pages/roster/show.matches.cs_unit') }}
                        </p>
                        <p class="text-[12px] text-text-gray">
                            <span class="text-white font-bold">{{ $match->getCsPerMinute() }}</span>
                            {{ __('pages/roster/show.matches.cs_per_min_suffix') }}
                        </p>
                    </div>

                    <div class="flex flex-1 basis-full flex-wrap content-center gap-0.5 sm:basis-48 md:flex-1">
                        @foreach ($itemIds as $itemId)
                        @if ((int) $itemId > 0)
                        <img
                            src="https://ddragon.leagueoflegends.com/cdn/{{ $ddragonVersion }}/img/item/{{ $itemId }}.png"
                            alt=""
                            class="size-5 shrink-0 rounded-sm border border-[#2C2D34] bg-bg-card/60 sm:size-10 md:size-5 xl:size-9">
                        @else
                        <span
                            class="size-5 shrink-0 rounded-sm border border-[#2C2D34] bg-black/30 sm:size-10"
                            aria-hidden="true"></span>
                        @endif
                        @endforeach
                    </div>

                    <div class="ml-auto flex flex-col text-right sm:ml-0">
                        <p class="font-semibold text-white">{{ $duration->minutes }}:{{ $duration->seconds }}</p>
                        <p class="text-[12px] text-text-gray">{{ $match->played_at->diffForHumans() }}</p>
                    </div>
                </div>
            </li>
            @endforeach
        </ul>
        @endif
    </section>
    @endif
</div>