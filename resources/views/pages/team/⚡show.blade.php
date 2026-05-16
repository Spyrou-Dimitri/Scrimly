<?php

use App\Enums\LolTier;
use App\Enums\RoleInGame;
use App\Enums\RoleInTeam;
use App\Enums\StatusInTeam;
use App\Models\Team;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::team')] class extends Component
{
    public Team $team;

    public function mount(string $slug, int|string $id): void
    {
        $contextTeam = currentTeam();

        if (! $contextTeam || $contextTeam->slug !== $slug) {
            abort(404);
        }

        $this->team = Team::query()->findOrFail($id);
    }

    #[Computed]
    public function starterMembers(): Collection
    {
        return $this->team
            ->members()
            ->wherePivot('is_starter', true)
            ->wherePivot('status', StatusInTeam::ACCEPTED)
            ->get();
    }

    public function openModalProposeScrim(int $teamId): void
    {
        $this->dispatch('open_modal', [
            'form' => 'scrims.propose-scrim',
            'model_id' => $teamId,
        ]);
    }
};
?>

@php
    $tier = LolTier::fromStarterAverageElo($team->starter_average_elo);
    $memberSince = $team->created_at->isoFormat('D MMMM YYYY');
@endphp

<div class="flex flex-col gap-12 lg:gap-16">
    <section class="flex flex-col gap-8">
        <div class="grid grid-cols-12 items-start gap-6 lg:gap-8">
            <div class="col-span-12 flex justify-center self-start lg:col-span-3 lg:justify-start">
                <div class="relative size-40 shrink-0 overflow-hidden  sm:size-44 lg:w-full lg:size-48">
                    <img
                        src="{{ $team->logo_url }}"
                        alt="{{ $team->name }}"
                        class="size-full object-cover"
                        loading="lazy"
                        >
                </div>
            </div>

            <div class="col-span-12 flex flex-col gap-4 lg:col-span-9">
                <div class="flex items-start justify-between gap-4 flex-row  flex-wrap items-start">
                    <h2 class="text-[32px] font-bold text-gold">
                        {{ $team->name }}
                    </h2>
                    <button
                        wire:click="openModalProposeScrim({{ $team->id }})"
                        class="cta-primary"
                        title="{{ __('pages/team/show.propose_scrim') }}"
                    >
                        {{ __('pages/team/show.propose_scrim') }}
                    </button>
                </div>

                @if (filled($team->description))
                    <div x-data="{ expanded: false, clamped: false }" 
                    x-init="nextTick() = $refs.description.scrollHeight > $refs.description.clientHeight"
                    class="max-w-3xl">
                        <p
                            x-ref="description"
                            x-bind:class="expanded ? '' : 'line-clamp-4'"
                        >{{ $team->description }}</p>
                        <button
                            x-show="clamped"
                            type="button"
                            class="mt-2 cursor-pointer text-sm font-medium text-gold hover:text-gold-light"
                            x-on:click="expanded = ! expanded"
                        >
                            <span x-show="! expanded">{{ __('pages/team/show.see_more') }}</span>
                            <span x-show="expanded" x-cloak>{{ __('pages/team/show.see_less') }}</span>
                        </button>
                    </div>
                @endif

                @if ($memberSince)
                    <p class="text-sm text-text-secondary">
                        {{ __('pages/team/show.member_since', ['date' => $memberSince]) }}
                    </p>
                @endif
            </div>

            <div class="col-span-12 grid grid-cols-12 gap-4 sm:gap-6 lg:col-span-12">
                <div class="col-span-12 bg-bg-widget p-4 basic-shadow sm:col-span-6 lg:col-span-3">
                    <p class="text-sm text-text-secondary">{{ __('pages/team/show.widget_average_elo') }}</p>
                    @if ($tier)
                        <div class="mt-2 flex items-center gap-2">
                            <img
                                src="{{ asset($tier->icon()) }}"
                                alt=""
                                class="size-8 shrink-0 object-contain"
                                width="32"
                                height="32"
                            >
                            <span @class(['text-base font-semibold', $tier->color()])>{{ $tier->label() }}</span>
                        </div>
                    @else
                        <p class="mt-2 text-base font-medium text-text-secondary">{{ __('pages/team/show.unranked') }}</p>
                    @endif
                </div>

                <div class="col-span-12 bg-bg-widget p-4 basic-shadow sm:col-span-6 lg:col-span-3">
                    <p class="text-sm text-text-secondary">{{ __('pages/team/show.widget_server') }}</p>
                    <p class="mt-2 text-base font-semibold">
                        <span class="{{ $team->server->color() }}">{{ $team->server->value }}</span>
                    </p>
                </div>

                <div class="col-span-12 bg-bg-widget p-4 basic-shadow sm:col-span-6 lg:col-span-3">
                    <p class="text-sm text-text-secondary">{{ __('pages/team/show.widget_goal') }}</p>
                    <p class="mt-2 text-base font-semibold">
                        <span class="{{ $team->goal->color() }}">{{ $team->goal->label() }}</span>
                    </p>
                </div>

                <div class="col-span-12 bg-bg-widget p-4 basic-shadow sm:col-span-6 lg:col-span-3">
                    <p class="text-sm text-text-secondary">{{ __('pages/team/show.widget_language') }}</p>
                    <p class="mt-2 text-base font-semibold">
                        <span class="{{ $team->language->color() }}">{{ $team->language->label() }}</span>
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section class="flex flex-col gap-6">
        <h2 id="team-roster-heading" class="text-[32px] font-bold text-white">
            {{ __('pages/team/show.roster_title') }}
        </h2>

        @if ($this->starterMembers->isEmpty())
            <p class="text-text-secondary">{{ __('pages/team/show.roster_empty') }}</p>
        @else
            <div class="grid grid-cols-12 gap-4 sm:gap-6">
                @foreach ($this->starterMembers as $member)
                    <div class="col-span-12 flex gap-3 bg-bg-widget p-4 basic-shadow md:col-span-6 lg:col-span-4">
                        <img
                            src="{{ $member->avatar_url }}"
                            alt=""
                            class="size-[60px] shrink-0 object-cover"
                            width="60"
                            height="60"
                        >
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-bold text-white">{{ $member->username }}</p>
                            <div class="mt-1 flex min-w-0 items-center gap-1.5 text-sm text-text-secondary">
                                @if ($member->pivot->roleInGame)
                                    <img
                                        src="{{ asset(RoleInGame::from($member->pivot->roleInGame)->icon()) }}"
                                        alt=""
                                        class="size-6 shrink-0 object-contain"
                                    >
                                    <span class="truncate">{{ RoleInGame::from($member->pivot->roleInGame)->label() }}</span>
                                @else
                                    <span class="truncate">{{ RoleInTeam::from($member->pivot->roleInTeam)->label() }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
</div>
