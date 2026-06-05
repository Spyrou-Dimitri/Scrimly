<?php

use App\Models\Team;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use App\Enums\StatusInTeam;
use App\Enums\LolServeur;
use App\Enums\LolGoal;
use App\Enums\LolTier;

new #[Layout('layouts::team')] class extends Component
{
    use WithPagination;

    public string $term = '';
    public ?string $elo = null;
    public ?LolGoal $goal = null;
    public ?LolServeur $server = null;
    #[Computed]
    public function teams()
    {
        $query = Team::query()
            ->withCount(['members' => function ($query) {
                $query->where('status', StatusInTeam::ACCEPTED);
            }])
            ->where('id', '!=', currentTeam()->id);

        if ($this->term !== '') {
            $query->where('name', 'like', '%'.$this->term.'%');
        }
        if ($this->goal !== null) {
            $query->where('goal', $this->goal);
        }
        if ($this->server !== null) {
            $query->where('server', $this->server);
        }
        $tierFilter = filled($this->elo) ? LolTier::tryFrom((string)$this->elo) : null;

        if ($tierFilter instanceof LolTier) {
            ['minInclusive' => $minInclusive, 'maxExclusive' => $maxExclusive] = $tierFilter->starterAverageEloInterval();
            $query->whereNotNull('starter_average_elo')
                ->where('starter_average_elo', '>=', $minInclusive);

            if ($maxExclusive !== null) {
                $query->where('starter_average_elo', '<', $maxExclusive);
            }
        }

        return $query->orderBy('name')->paginate(8);
    }

    public function updatedTerm(): void
    {
        $this->resetPage();
    }

    public function updatedElo(): void
    {
        $this->resetPage();
    }

    public function updatedGoal(): void
    {
        $this->resetPage();
    }

    public function updatedServer(): void
    {
        $this->resetPage();
    }
};
?>

<div>
    <section class="flex flex-col gap-6" aria-labelledby="scrims-find-heading">
        <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <h2 id="scrims-find-heading" class="text-2xl font-bold text-white md:text-[32px]">
                    {{ __('pages/scrims/find.title') }}
                </h2>
                <p class="mt-1 text-sm text-text-secondary">
                    {{ __('pages/scrims/find.page_description') }}
                </p>
            </div>
        </div>

        <div
            class="grid grid-cols-1 gap-4 bg-bg-widget p-4 basic-shadow sm:grid-cols-2 sm:gap-5 sm:p-6 md:gap-6 lg:grid-cols-12 lg:items-end lg:gap-5 xl:gap-6"
        >
            <div class="sm:col-span-2 lg:col-span-3">
                <x-forms.input
                    :type="'search'"
                    wire:model.live.debounce.300ms="term"
                    :name="'team-find-search'"
                    :label="__('pages/scrims/find.search_label')"
                    :placeholder="__('pages/scrims/find.search_placeholder')"
                />
            </div>
            <div class="sm:col-span-1 lg:col-span-3">
                <x-forms.select
                    wire:model.live.debounce.300ms="elo"
                    :name="'team-find-elo'"
                    :label="__('pages/scrims/find.elo_label')"
                    :options="LolTier::cases()"
                    :disabled="__('pages/scrims/find.elo_disabled')"
                />
            </div>
            <div class="sm:col-span-1 lg:col-span-3">
                <x-forms.select
                    wire:model.live.debounce.300ms="server"
                    :name="'team-find-server'"
                    :label="__('pages/scrims/find.server_label')"
                    :disabled="__('pages/scrims/find.server_disabled')"
                    :options="LolServeur::cases()"
                />
            </div>
            <div class="sm:col-span-2 lg:col-span-3">
                <x-forms.select
                    wire:model.live.debounce.150ms="goal"
                    :name="'team-find-goal'"
                    :label="__('pages/scrims/find.goal_label')"
                    :options="LolGoal::cases()"
                    :disabled="__('pages/scrims/find.goal_disabled')"
                />
            </div>
        </div>

        @if ($this->teams->count() > 0)
            <ul class="grid grid-cols-12 gap-4 md:gap-6">
                @foreach ($this->teams as $team)
                    <li class="col-span-12 md:col-span-6">
                        <x-cards.team-finder :team="$team" />
                    </li>
                @endforeach
            </ul>

            <div class="mt-4">
                {{ $this->teams->links() }}
            </div>
        @else
            <p class="rounded-md border border-white/10 bg-bg-widget p-6 text-center text-text-secondary">
                {{ __('pages/scrims/find.empty') }}
            </p>
        @endif
    </section>
</div>
