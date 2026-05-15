<?php

use App\Models\Team;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use App\Enums\StatusInTeam;

new #[Layout('layouts::team')] class extends Component
{
    use WithPagination;

    public string $term = '';

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

        return $query->orderBy('name')->paginate(8);
    }

    public function updatedTerm(): void
    {
        $this->resetPage();
    }
};
?>

<div>
    <section class="flex flex-col gap-6">
        <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white md:text-[32px]">
                    {{ __('pages/scrims/find.title') }}
                </h2>
                <p class="mt-1 text-sm text-text-secondary">
                    {{ __('pages/scrims/find.page_description') }}
                </p>
            </div>
        </div>

        <div class="p-6 bg-bg-widget basic-shadow">
            <x-forms.input
                :type="'search'"
                wire:model.live.debounce.150ms="term"
                :name="'team-find-search'"
                :label="__('pages/scrims/find.search_placeholder')"
                :placeholder="__('pages/scrims/find.search_placeholder')"
            />
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
