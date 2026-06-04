<?php

use App\Enums\LolTier;
use App\Enums\RoleInGame;
use App\Enums\RoleInTeam;
use App\Enums\StatusInTeam;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\ScrimRequest;
use App\Enums\StatusScrimRequest;
use Illuminate\Support\Facades\Gate;

new #[Layout('layouts::team')] class extends Component
{
    public Team $team;
    public ?ScrimRequest $alreadySendScrimRequest = null;
    public ?ScrimRequest $alreadyReceiveScrimRequest = null;

    public function mount(string $slug, int|string $id): void
    {
        $contextTeam = currentTeam();

        if (! $contextTeam || $contextTeam->slug !== $slug) {
            abort(404);
        }

        $this->team = Team::query()->findOrFail($id);
        $this->alreadySendScrimRequest = ScrimRequest::query()->where('receiver_team_id', $this->team->id)->where('requester_team_id', currentTeam()->id)->where('status', StatusScrimRequest::PENDING)->first();
        $this->alreadyReceiveScrimRequest = ScrimRequest::query()->where('requester_team_id', $this->team->id)->where('receiver_team_id', currentTeam()->id)->where('status', StatusScrimRequest::PENDING)->first();
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
        if (Gate::denies('create', [ScrimRequest::class, $this->team])) {
            $this->dispatch('toast', [
                'title' => __('pages/team/show.already_send_scrim_request'),
                'message' => __('pages/team/show.already_send_scrim_request_message'),
                'type' => 'error',
            ]);

            return;
        }

        $this->dispatch('open_modal', [
            'form' => 'scrims.propose-scrim',
            'model_id' => $teamId,
        ]);
    }
    public function openModalShowScrimRequest(int $scrimRequestId): void
    {
        $this->dispatch('open_modal', [
            'form' => 'scrims.show-scrim-request',
            'model_id' => $scrimRequestId,
        ]);
    }
};
?>

@php
$tier = LolTier::fromStarterAverageElo($team->starter_average_elo);
$memberSince = $team->created_at->isoFormat('D MMMM YYYY');
$canManageTeam = Gate::allows('manageTeam', User::class);
@endphp

<div class="flex flex-col gap-12 lg:gap-16">
    <section class="flex flex-col gap-8">
        <div class="grid grid-cols-12 items-start gap-6 lg:gap-8">
            <div class="col-span-12 flex justify-center self-start lg:col-span-3 lg:justify-start">
                <div class="relative size-40 shrink-0 overflow-hidden  sm:size-44 lg:w-full lg:size-48">
                    <x-team-logo
                        :team="$team"
                        preset="team-hero"
                        class="size-full object-cover" />
                </div>
            </div>

            <div class="col-span-12 flex flex-col gap-4 lg:col-span-9">
                <div class="flex  justify-between gap-4 flex-row  flex-wrap items-center">
                    <div class="flex flex-wrap items-center gap-4">
                        <h2 class="text-[32px] font-bold text-gold">
                            {{ $team->name }}
                        </h2>
                        @if ($team->code)
                        <div
                            class="relative"
                            x-data="{ copied: false }"
                            x-on:click="
                                navigator.clipboard.writeText(@js($team->code));
                                copied = true;
                                setTimeout(() => copied = false, 2000);
                            ">
                            <div class="flex group cursor-pointer flex-wrap items-center gap-2 rounded-md bg-bg-widget p-3 text-white">
                                <p class="font-bold text-text-secondary">
                                    Code :
                                    <span class="text-white group-hover:text-gold">{{ $team->code }}</span>
                                </p>
                                <flux:icon name="clipboard" variant="outline" class="size-5 shrink-0 group-hover:text-gold" />
                            </div>
                            <span
                                x-show="copied"
                                x-cloak
                                x-transition
                                class="pointer-events-none absolute left-full top-1/2 z-10 ml-2 -translate-y-1/2 whitespace-nowrap rounded-md bg-gold px-2.5 py-1 text-xs font-semibold text-bg-main shadow-basic"
                            >{{ __('pages/team/show.code_copied') }}</span>
                        </div>
                        @endif
                    </div>
                    @if ($canManageTeam)
                    <x-cta :class="'primary'" :href="route('team.edit', ['slug' => currentTeam()->slug, 'id' => $team->id])" :title="__('pages/team/show.edit_team')">
                        {{ __('pages/team/show.edit_team') }}
                    </x-cta>
                    @endif
                    @if ($this->alreadySendScrimRequest)
                    <div class="flex items-center gap-2 bg-red-900/60 p-2 text-left text-white">
                        <flux:icon name="exclamation-triangle" variant="outline" class="size-12 shrink-0" />
                        <p class="text-sm sm:text-base">
                            {{ __('pages/team/show.already_send_scrim_request') }}
                        </p>
                    </div>
                    @endif
                    @if ($this->alreadyReceiveScrimRequest)
                    <div class="flex items-center gap-2 bg-red-900/60 p-2 text-left text-white">
                        <flux:icon name="exclamation-triangle" variant="outline" class="size-12 shrink-0" />
                        <p class="text-sm sm:text-base">
                            {{ __('pages/team/show.already_receive_scrim_request') }}
                            <button
                                type="button"
                                wire:click="openModalShowScrimRequest({{ $alreadyReceiveScrimRequest->id }})"
                                class="cursor-pointer text-gold hover:underline">
                                {{ __('pages/team/show.already_send_scrim_request_view') }}
                            </button>
                        </p>
                    </div>
                    @endif
                    @can('create', [ScrimRequest::class, $team])
                    <button
                        wire:click="openModalProposeScrim({{ $team->id }})"
                        class="cta-primary"
                        title="{{ __('pages/team/show.propose_scrim') }}">
                        {{ __('pages/team/show.propose_scrim') }}
                    </button>
                    @endcan
                </div>

                @if (filled($team->description))
                <div x-data="{ expanded: false, clamped: false }"
                    x-init="nextTick() = $refs.description.scrollHeight > $refs.description.clientHeight"
                    class="max-w-3xl">
                    <p
                        x-ref="description"
                        x-bind:class="expanded ? '' : 'line-clamp-4'">{{ $team->description }}</p>
                    <button
                        x-show="clamped"
                        type="button"
                        class="mt-2 cursor-pointer text-sm font-medium text-gold hover:text-gold-light"
                        x-on:click="expanded = ! expanded">
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
                            height="32">
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
                <x-user-avatar
                    :user="$member"
                    preset="team-row"
                    alt=""
                    class="size-[60px] shrink-0 object-cover"
                    width="60"
                    height="60" />
                <div class="min-w-0 flex-1">
                    <p class="truncate font-bold text-white">{{ $member->username }}</p>
                    <div class="mt-1 flex min-w-0 items-center gap-1.5 text-sm text-text-secondary">
                        @if ($member->pivot->roleInGame)
                        <img
                            src="{{ asset(RoleInGame::from($member->pivot->roleInGame)->icon()) }}"
                            alt=""
                            class="size-6 shrink-0 object-contain">
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