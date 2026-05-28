@props([
'teamMember',
])

@php
use App\Enums\RoleInTeam;

$memberUser = $teamMember->user;
$tierLine = null;
if ($memberUser->tier) {
$tier = $memberUser->tier;
$tierLine = $tier->label();
if ($tier->isApex() && $memberUser->lp !== null && $memberUser->lp !== '') {
$tierLine .= ' • '.$memberUser->lp.' LP';
} elseif (filled($memberUser->rank)) {
$tierLine .= ' • '.$memberUser->rank;
}
}
@endphp

<article @click="$el.querySelector('[data-profil-link]')?.click()" {{ $attributes->merge(['class' => 'relative cursor-pointer border-l-2 border-gold flex min-h-full flex-col bg-bg-card p-4 basic-shadow md:p-5 card-animated-border']) }}>
    <span class="card-animated-border-right-edge" aria-hidden="true"></span>
    <div class="relative">
        <div class="relative overflow-hidden">
            <x-user-avatar
                :user="$memberUser"
                preset="player-card"
                class="aspect-square w-full object-cover"
            />
            
        </div>
        <div class="absolute right-2 top-2 z-10 flex items-center gap-2" @click.stop>
            <span class="inline-flex flex-1 items-center gap-1.5 bg-bg-widget px-3 py-2 text-xs font-medium text-white">
                <span class="size-1.5 shrink-0 rounded-full bg-task-done" aria-hidden="true"></span>
                {{ __('pages/roster/index.online') }}
            </span>
            <div
                class="relative shrink-0"
                x-data="{ open: false }"
                @click.outside="open = false"
                @keydown.escape.window="open = false">
                @can('manageTeam', \App\Models\User::class)
                <button
                    type="button"
                    class="flex cursor-pointer size-8 items-center justify-center bg-bg-widget border border-transparent hover:border-gold transition-all duration-150 ease-in-out group text-white"
                    aria-label="{{ __('pages/roster/index.member_menu') }}"
                    aria-haspopup="menu"
                    :aria-expanded="open"
                    @click="open = ! open">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 transition-all duration-150 ease-in-out group-hover:text-gold" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75a.75.75 0 110-1.5.75.75 0 010 1.5zM12 12.75a.75.75 0 110-1.5.75.75 0 010 1.5zM12 18.75a.75.75 0 110-1.5.75.75 0 010 1.5z" />
                    </svg>
                </button>
                <div
                    x-show="open"
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    x-cloak
                    class="absolute right-0 top-full z-[60] mt-1 flex w-max min-w-0 flex-col bg-bg-widget basic-shadow"
                    role="menu"
                    aria-orientation="vertical">
                    @if (!$teamMember->is_starter)
                    <button
                        type="button"
                        class="flex w-full cursor-pointer items-center justify-end gap-2 whitespace-nowrap px-3 py-2.5 text-right text-sm font-medium text-white transition-colors duration-150 hover:bg-white/5 hover:text-gold"
                        role="menuitem"
                        wire:click="openModalPromoteToStarter({{ $teamMember->id }})"
                        @click="open = false">
                        <flux:icon name="star" class="size-4 hover:text-gold" />
                        {{ __('pages/roster/index.menu_promote_starter') }}
                    </button>
                    @else
                    <button
                        type="button"
                        class="flex w-full cursor-pointer items-center justify-end gap-2 whitespace-nowrap px-3 py-2.5 text-right text-sm font-medium text-white transition-colors duration-150 hover:bg-white/5 hover:text-gold"
                        role="menuitem"
                        wire:click="openModalSendToBench({{ $teamMember->id }})"
                        @click="open = false">
                        <flux:icon name="user-minus" class="size-4 hover:text-gold" />
                        {{ __('pages/roster/index.menu_demote_to_bench') }}
                    </button>
                    @endif
                    @if ($memberUser->id !== auth()->id())
                    <button
                        type="button"
                        class="flex w-full cursor-pointer items-center justify-end gap-2 whitespace-nowrap px-3 py-2.5 text-right text-sm font-medium text-red-400 transition-colors duration-150 hover:bg-red-500/10 hover:text-red-300"
                        role="menuitem"
                        wire:click="openModalKickTeamMember({{ $teamMember->id }})"
                        @click="open = false">
                        <flux:icon name="trash" class="size-4 hover:text-gold" />
                        {{ __('pages/roster/index.menu_kick_member') }}
                    </button>
                    @endif
                </div>
                @endcan
            </div>
        </div>
    </div>
    <div class="mt-4 flex min-w-0 flex-1 flex-col gap-4 justify-center">
        <div class="flex min-w-0 flex-1 flex-col gap-3">
            <div class="relative z-[1] flex min-w-0 flex-row items-stretch gap-2 md:flex-col md:gap-3 lg:flex-row lg:items-stretch lg:gap-3 xl:gap-4">
                <div class="flex min-w-0 flex-1 flex-col gap-1 lg:basis-0 lg:grow-[3]">
                    <h4 class="truncate text-xl font-bold text-gold">{{ $memberUser->username }}</h4>
                    @if ($memberUser->riot_tag)
                    <p class="truncate font-mono text-sm lg:text-xs 2xl:text-sm text-text-gray">{{ $memberUser->riot_tag }}</p>
                    @endif
                    <div class="flex w-full min-w-0 items-center gap-2 text-sm text-white">
                        @if ($memberUser->tier && $tierLine)
                        <img src="{{ asset($memberUser->tier->icon()) }}" class="size-7 shrink-0" alt="{{ $memberUser->tier->label() }}">
                        <p class="min-w-0 flex-1 truncate">{{ $tierLine }}</p>
                        @else
                        <p class="flex-1">—</p>
                        @endif
                    </div>
                </div>
                <div class="flex min-h-0 min-w-0 w-fit shrink-0 flex-col justify-center self-stretch items-end md:w-full md:items-start lg:w-auto lg:basis-0 lg:grow lg:shrink lg:min-w-0 lg:items-end">
                    <div class="flex max-w-full min-w-0 flex-col items-center gap-1 text-lg font-semibold text-white md:items-start lg:items-center">
                        @if ($teamMember->roleInTeam === RoleInTeam::COACH || $teamMember->roleInTeam === RoleInTeam::STAFF)
                        <span class="min-w-0 max-w-full truncate text-center md:text-start lg:text-end">{{ $teamMember->roleInTeam->label() }}</span>
                        @elseif ($teamMember->roleInGame)
                        <img src="{{ asset($teamMember->roleInGame->icon()) }}" class="size-7 shrink-0" alt="{{ $teamMember->roleInGame->label() }}">
                        <span class="min-w-0 max-w-full truncate text-center md:text-start lg:text-end">{{ $teamMember->roleInGame->label() }}</span>
                        @else
                        <span class="text-text-secondary">—</span>
                        @endif
                    </div>
                </div>
            </div>

        </div>

        <div class="mt-auto">
            <x-cta  wire:navigate data-profil-link :class="'cta-primary'" :widthFull="true" :href="route('roster.show', [currentTeam()->slug, $teamMember->id])" :title="__('pages/roster/index.view_profile_title') . ' ' . $memberUser->username">
                {{ __('pages/roster/index.view_profile') }}
            </x-cta>
        </div>
    </div>
</article>