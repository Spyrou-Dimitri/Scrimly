<?php

use App\Enums\LolTier;
use App\Enums\RoleInTeam;
use App\Enums\StatusApplication;
use App\Enums\StatusScrimRequest;
use App\Enums\StatusTask;
use App\Livewire\Actions\Logout;
use App\Models\ScrimRequest;
use App\Models\TeamApplication;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public User $currentUser;

    public function mount(): void
    {
        $this->currentUser = Auth::user();
    }

    #[Computed]
    public function pendingScrimRequestsCount(): int
    {
        return ScrimRequest::query()
            ->where('receiver_team_id', currentTeam()->id)
            ->where('status', StatusScrimRequest::PENDING)
            ->count();
    }

    #[Computed]
    public function pendingCandidatesCount(): int
    {
        return TeamApplication::query()
            ->where('team_id', currentTeam()->id)
            ->where('status', StatusApplication::PENDING)
            ->count();
    }

    #[Computed]
    public function pendingHomeworkCount(): int
    {
        $member = currentMember();

        if (! $member) {
            return 0;
        }
        if ($member->roleInTeam === RoleInTeam::COACH || $member->roleInTeam === RoleInTeam::STAFF) {
            return 0;
        }

        return $member->tasks()
            ->whereIn('status', [StatusTask::TODO, StatusTask::IN_PROGRESS])
            ->count();
    }

    #[On('refresh_scrims')]
    #[On('refresh_candidates')]
    #[On('refresh_tasks')]
    public function refreshNavBadges(): void
    {
        unset(
            $this->pendingScrimRequestsCount,
            $this->pendingCandidatesCount,
            $this->pendingHomeworkCount,
        );
    }

    public function navItems(): array
    {
        return [
            [
                'label' => __('layouts/team.nav.dashboard'),
                'icon' => 'squares-2x2',
                'activeRoute' => 'dashboard',
                'href' => route('dashboard', ['slug' => currentTeam()->slug]),
            ],
            [
                'label' => __('layouts/team.nav.calendar'),
                'icon' => 'calendar-days',
                'activeRoute' => 'calendar.*',
                'href' => route('calendar.index', ['slug' => currentTeam()->slug]),
            ],
            [
                'label' => __('layouts/team.nav.scrims'),
                'icon' => 'trophy',
                'activeRoute' => 'scrims.*',
                'href' => route('scrims.index', ['slug' => currentTeam()->slug]),
                'badge' => $this->pendingScrimRequestsCount,
                'badgeTest' => 'sidebar-nav-badge-scrims',
                'badgeAria' => __('layouts/team.nav_badge_scrims_aria', ['count' => $this->pendingScrimRequestsCount]),
            ],
            [
                'label' => __('layouts/team.nav.roster'),
                'icon' => 'user-group',
                'activeRoute' => 'roster.*',
                'href' => route('roster.index', ['slug' => currentTeam()->slug]),
                'badge' => $this->pendingCandidatesCount,
                'badgeTest' => 'sidebar-nav-badge-roster',
                'badgeAria' => __('layouts/team.nav_badge_roster_aria', ['count' => $this->pendingCandidatesCount]),
            ],
            [
                'label' => __('layouts/team.nav.homework'),
                'icon' => 'book-open',
                'activeRoute' => 'tasks.*',
                'href' => route('tasks.index', ['slug' => currentTeam()->slug]),
                'badge' => $this->pendingHomeworkCount,
                'badgeTest' => 'sidebar-nav-badge-homework',
                'badgeAria' => __('layouts/team.nav_badge_homework_aria', ['count' => $this->pendingHomeworkCount]),
            ],
            [
                'label' => __('layouts/team.nav.chat'),
                'icon' => 'chat-bubble-left-right',
                'activeRoute' => 'chats.*',
                'href' => route('chats.index', ['slug' => currentTeam()->slug]),
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
    x-cloak
    @sidebar-toggle.window="open = ! open"
    @sidebar-close.window="open = false"
    @keydown.escape.window="open = false"
    class="contents">
    <div
        x-show="open"
        x-cloak
        x-transition.opacity.duration.150ms
        @click="$dispatch('sidebar-close')"
        class="fixed inset-0 z-30 bg-black/70 lg:hidden"
        aria-hidden="true"></div>

    <aside
        :class="open ? 'translate-x-0' : 'translate-x-full'"
        class="fixed inset-y-0 flex flex-col justify-between right-0 z-40 w-64
               bg-bg-widget border-l lg:border-l-0 lg:border-r basic-shadow border-[#2C2D34]
               transform transition-transform duration-150 ease-in-out
               lg:static lg:translate-x-0 lg:row-span-2 lg:col-start-1 lg:row-start-1"
        aria-label="{{ __('layouts/team.main_navigation_aria') }}">
        <h2 class="sr-only">
            {{ __('layouts/team.sidebar_heading') }}
        </h2>
        <div class="flex h-16 items-center px-6 flex-shrink-0">
            <a
                href="{{ route('home')}}"`
                wire:navigate
                class="text-gold text-2xl font-bold tracking-wide">
                ScrimlyLol
            </a>
        </div>

        <nav class="flex-1 overflow-y-auto px-4 py-6 flex flex-col gap-2">
            <h3 class="sr-only">
                {{ __('layouts/team.main_navigation_aria') }}
            </h3>
            @foreach ($this->navItems() as $item)
            @php
            $isActive = $item['activeRoute'] !== null && request()->routeIs($item['activeRoute']);
            @endphp

            <a
                href="{{ $item['href'] }}"
                @if ($item['activeRoute']) wire:navigate @endif
                @click="open = false"
                @class([ 'flex items-center gap-3 px-4 py-3 transition-colors duration-150' , 'border border-gold bg-bg-card text-gold'=> $isActive,
                'text-white hover:text-gold hover:bg-white/5' => ! $isActive,
                ])
                @if ($isActive) aria-current="page" @endif
                >
                <flux:icon name="{{ $item['icon'] }}" class="size-5 flex-shrink-0" />
                <span class="font-medium">{{ $item['label'] }}</span>

                @if (($item['badge'] ?? 0) > 0)
                    <span
                        @isset($item['badgeTest']) data-test="{{ $item['badgeTest'] }}" @endisset
                        aria-label="{{ $item['badgeAria'] ?? '' }}"
                        class="ml-auto flex size-5 shrink-0 items-center justify-center rounded-full bg-input-error text-xs font-bold text-white">
                        {{ ($item['badge'] ?? 0) > 99 ? '99+' : $item['badge'] }}
                    </span>
                @endif
            </a>
            @endforeach
        </nav>

        @php
            

            $team = currentTeam();
            $member = currentMember();
            $averageEloTier = LolTier::fromStarterAverageElo($team->starter_average_elo);
        @endphp

        <div class="flex flex-shrink-0 flex-col gap-3 p-4">
            @if ($member)
            <a
                href="{{ route('roster.show', ['slug' => $team->slug, 'id' => $member->id]) }}"
                wire:navigate
                title="{{ __('layouts/team.view_member_profile_title') }}"
                data-test="sidebar-member-profile-link"
                @click="open = false"
                class="flex items-center gap-3 bg-bg-card p-3 transition-colors duration-150 hover:bg-white/5">
                <x-user-avatar
                    :user="$member->user"
                    preset="thumbnail"
                    class="size-10 shrink-0 rounded-lg object-cover"
                />
                <div class="min-w-0 flex-1">
                    <p class="truncate font-semibold text-white">{{ $member->user->username }}</p>
                    @if ($member->roleInTeam === RoleInTeam::COACH || $member->roleInTeam === RoleInTeam::STAFF)
                        <p class="truncate text-sm text-text-secondary">{{ $member->roleInTeam->label() }}</p>
                    @elseif ($member->roleInGame)
                        <div class="flex items-center gap-2">
                            <img src="{{ asset($member->roleInGame->icon()) }}" class="size-5 shrink-0" alt="{{ $member->roleInGame->label() }}">
                            <p class="truncate text-sm text-text-secondary">{{ $member->roleInGame->label() }}</p>
                        </div>
                    @endif
                </div>
            </a>
            @endif

            <a
                href="{{ route('team.show', ['slug' => $team->slug, 'id' => $team->id]) }}"
                wire:navigate
                title="{{ __('layouts/team.view_team_profile_title') }}"
                @click="open = false"
                class="flex items-center gap-3 bg-bg-card p-3 transition-colors duration-150 hover:bg-white/5">
                <div class="size-10 shrink-0 overflow-hidden rounded-lg bg-white/5">
                    <x-team-logo
                        :team="$team"
                        preset="thumbnail"
                        class="size-full object-cover"
                    />
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate font-semibold text-white">{{ $team->name }}</p>
                    @if ($averageEloTier)
                    <div class="flex items-center gap-2">
                        <img src="{{ asset($averageEloTier->icon()) }}" class="size-5" alt="{{ $averageEloTier->label() }}">
                        <p @class(['truncate text-sm', $averageEloTier->color()])>{{ $averageEloTier->label() }}</p>
                    </div>
                    @else
                        <p class="truncate text-sm text-text-secondary">{{ __('pages/team/show.unranked') }}</p>
                    @endif
                </div>
            </a>

            <button
                type="button"
                title="{{ __('layouts/team.logout') }}"
                aria-label="{{ __('layouts/team.logout_aria') }}"
                wire:click="logout"
                class="cta-danger cta-danger--outline">
                <flux:icon name="power" class="size-4 shrink-0" />
                <span class="font-medium">{{ __('layouts/team.logout') }}</span>
            </button>
        </div>
    </aside>
</div>