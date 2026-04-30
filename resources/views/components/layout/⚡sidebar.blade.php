<?php

use App\Livewire\Actions\Logout;
use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    public User $currentUser;

    public function mount(): void
    {
        $this->currentUser = Auth::user();
    }

    public function navItems(): array
    {
        return [
            [
                'label' => __('layouts/team.nav.dashboard'),
                'icon' => 'squares-2x2',
                'route' => 'dashboard',
                'href' => route('dashboard'),
            ],
            [
                'label' => __('layouts/team.nav.calendar'),
                'icon' => 'calendar-days',
                'route' => null,
                'href' => '#',
            ],
            [
                'label' => __('layouts/team.nav.scrims'),
                'icon' => 'trophy',
                'route' => null,
                'href' => '#',
            ],
            [
                'label' => __('layouts/team.nav.roster'),
                'icon' => 'user-group',
                'route' => 'roster.index',
                'href' => route('roster.index', ['slug' => currentTeam()->slug]),
            ],
            [
                'label' => __('layouts/team.nav.homework'),
                'icon' => 'book-open',
                'route' => null,
                'href' => '#',
            ],
            [
                'label' => __('layouts/team.nav.statistics'),
                'icon' => 'chart-bar',
                'route' => null,
                'href' => '#',
            ],
            [
                'label' => __('layouts/team.nav.chat'),
                'icon' => 'chat-bubble-left-right',
                'route' => null,
                'href' => '#',
            ],
        ];
    }


    public function logout(Logout $logout): void
    {
        $logout();
        $this->currentUser->current_team_id = null;
        $this->currentUser->save();

        $this->redirect(route('login'));
    }
};
?>

<div
    x-data="{ open: false }"
    @sidebar-toggle.window="open = ! open"
    @sidebar-close.window="open = false"
    @keydown.escape.window="open = false"
    class="contents">
    <div
        x-show="open"
        x-cloak
        x-transition.opacity.duration.150ms
        @click="open = false"
        class="fixed inset-0 z-30 bg-black/70 lg:hidden"
        aria-hidden="true"></div>

    <aside
        :class="open ? 'translate-x-0' : '-translate-x-full'"
        class="fixed inset-y-0 flex flex-col justify-between left-0 z-40 w-64
               bg-bg-widget border-r basic-shadow border-[#2C2D34]
               transform transition-transform duration-150 ease-in-out
               lg:static lg:translate-x-0 lg:row-span-2 lg:col-start-1 lg:row-start-1"
        aria-label="{{ __('layouts/team.main_navigation_aria') }}">
        <h2 class="sr-only">
            Barre latérale de navigation
        </h2>
        <div class="flex h-16 items-center px-6 flex-shrink-0">
            <a
                href="{{ route('dashboard') }}"
                wire:navigate
                class="text-gold text-2xl font-bold tracking-wide">
                Scrimly
            </a>
        </div>

        <nav class="flex-1 overflow-y-auto px-4 py-6 flex flex-col gap-2">
            <h3 class="sr-only">
                Navigation principale
            </h3>
            @foreach ($this->navItems() as $item)
            @php
            $isActive = $item['route'] !== null && request()->routeIs($item['route']);
            @endphp

            <a
                href="{{ $item['href'] }}"
                @if ($item['route']) wire:navigate @endif
                @click="open = false"
                @class([ 'flex items-center gap-3 px-4 py-3 transition-colors duration-150' , 'border border-gold bg-bg-card text-gold'=> $isActive,
                'text-white hover:text-gold hover:bg-white/5' => ! $isActive,
                ])
                @if ($isActive) aria-current="page" @endif
                >
                <flux:icon name="{{ $item['icon'] }}" class="size-5 flex-shrink-0" />
                <span class="font-medium">{{ $item['label'] }}</span>
            </a>
            @endforeach
        </nav>

        <div class="p-4 flex-shrink-0">
            <button
                type="button"
                title="{{ __('layouts/team.logout') }}"
                wire:click="logout"
                class="flex w-full items-center justify-center gap-2 px-4 py-3
                       bg-red-900/40 hover:bg-red-900/60
                       text-white border border-red-900/60
                       transition-colors duration-150 cursor-pointer">
                <flux:icon name="power" class="size-4" />
                <span class="font-medium">{{ __('layouts/team.logout') }}</span>
            </button>
        </div>
    </aside>
</div>