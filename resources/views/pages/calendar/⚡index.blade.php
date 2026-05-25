<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Collection;
use App\Models\Scrim;
use App\Models\Absence;
use Carbon\Carbon;
use App\Models\Event;
new #[Layout('layouts::team')] class extends Component

{
    public array $events;
    public function mount(): void
    {
        $scrims = Scrim::where('team_id', currentTeam()->id)
            ->with('opponentTeam:id,name')
            ->get()
            ->map(fn(Scrim $scrim) => [
                'title' => ' - ' . $scrim->opponentTeam->name,
                'start' => $scrim->scheduled_at,
                'url' => route('scrims.show', [
                    'slug' => currentTeam()->slug,
                    'id' => $scrim->id,
                ]),
                'backgroundColor' => '#D4AF37',
                'extendedProps' => ['type' => 'scrim'],
            ])
            ->all();
        $events = Event::where('team_id', currentTeam()->id)
        ->get()
        ->map(fn(Event $event) => [
            'title' => $event->title,
            'start' => $event->date->format('Y-m-d'),
            'allDay' => $event->all_day,
            'backgroundColor' => $event->type->color(),
            'extendedProps' => ['type' => $event->type->label()],
        ]);
        $this->events = collect($scrims)->concat(collect($events))->values()->all();

    }
    public function handleDateClick(string $date): void
    {
        $this->dispatch('open_modal', [
            'form' => 'calendar.show-a-day',
            'model_id' => Carbon::parse($date)->format('Y-m-d'),
        ]);

    }
};
?>

<div>
    <section class="flex flex-col gap-6">
        <h2 class="text-[32px] font-bold">
            {{ __('pages/calendar/index.title') }}
        </h2>
        <x-calendar.legend />
        <div id="calendar" wire:ignore data-events='@json($events)'></div>
    </section>
</div>