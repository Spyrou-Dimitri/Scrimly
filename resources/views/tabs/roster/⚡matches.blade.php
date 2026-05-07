<?php

use App\Models\TeamMember;
use Illuminate\Support\Collection;
use Livewire\Attributes\Locked;
use Livewire\Component;

new class extends Component
{
    #[Locked]
    public TeamMember $teamMember;

    public Collection $matches;

    public ?int $riotProfileId = null;

    public function mount(TeamMember $teamMember): void
    {
        $this->teamMember = $teamMember;

        $riot = $teamMember->user->riotProfile;

        if ($riot === null) {
            $this->matches = collect();

            return;
        }

        $this->riotProfileId = $riot->id;
        $this->matches = $riot->riotMatches()
            ->orderByDesc('played_at')
            ->get();
    }
}; ?>

<section class="flex flex-col gap-4" aria-labelledby="roster-matches-heading">
    @php
    $ddragonVersion = config('riot.ddragon_version');
    @endphp

    <h3 id="roster-matches-heading" class="font-spaceGrotesk text-2xl font-bold text-white">
        {{ __('pages/roster/show.matches.section_title') }}
    </h3>

    @if ($riotProfileId === null)
        <p class="text-base text-text-gray">{{ __('pages/roster/show.matches.no_riot_profile') }}</p>
    @elseif ($matches->isEmpty())
        <p class="text-base text-text-gray">{{ __('pages/roster/show.matches.empty') }}</p>
    @else
        <ul class="flex flex-col gap-4" role="list">
            @foreach ($matches as $match)
                @php
                    $duration = $match->getDurationGameMinutes();
                    $itemIds = array_values($match->items ?? []);
                    $itemIds = array_pad($itemIds, 7, 0);
                    $itemIds = array_slice($itemIds, 0, 7);
                @endphp
                <li
                    class="flex shadow-basic bg-bg-widget">
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
