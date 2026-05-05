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
            ->with(['team', 'user.riotProfile.riotMatches'])
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
$recentChampions = $riot->riotMatches->pluck('champion_name')->unique()->take(3)->toArray();
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
                            <img src="https://ddragon.leagueoflegends.com/cdn/13.22.1/img/champion/{{ $champion }}.png" alt="Champion" class="aspect-square w-12 shrink-0 rounded border border-[#2C2D34] bg-bg-card/60">
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
</div>