<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Scrim;
use Carbon\Carbon;
use App\Models\Event;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;
new #[Layout('layouts::team')] class extends Component
{

    public function render()
    {
        return $this->view()->title(__('pages/calendar/index.title'));
    }

    #[Computed]
    public function events(): array
    {
        $scrims = Scrim::where('team_id', currentTeam()->id)
            ->with('opponentTeam:id,name')
            ->get()
            ->map(fn (Scrim $scrim) => [
                'title' => ' - '.$scrim->opponentTeam->name,
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
            ->map(fn (Event $event) => [
                'title' => $event->title,
                'start' => $event->scheduled_start,
                'end' => $event->scheduled_end,
                'allDay' => $event->all_day,
                'backgroundColor' => $event->type->color(),
                'extendedProps' => ['type' => $event->type->label()],
            ]);
        return collect($scrims)->concat(collect($events))->values()->all();
    }

    public function handleDateClick(string $date): void
    {
        $this->dispatch('open_modal', [
            'form' => 'calendar.show-a-day',
            'model_id' => Carbon::parse($date)->format('Y-m-d'),
        ]);
    }
    #[On('event-created')]
    public function refreshCalendar(): void
    {
        unset($this->events);
        $this->dispatch('calendar-refreshed', events: $this->events);
    }
    
    
};
?>

<div data-calendar="calendar">
    <section class="flex flex-col gap-6" aria-labelledby="calendar-index-heading">
        <h2 id="calendar-index-heading" class="text-[32px] font-bold">
            {{ __('pages/calendar/index.title') }}
        </h2>
        <x-calendar.legend />
        <div id="calendar" wire:ignore data-events='@json($this->events)'></div>
    </section>
</div>
