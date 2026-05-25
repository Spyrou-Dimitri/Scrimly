<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Collection;
use App\Models\Scrim;
use App\Models\Absence;

new #[Layout('layouts::team')] class extends Component

{
    public array $events;
    public function mount(): void
    {
        $scrims = Scrim::where('team_id', currentTeam()->id)
            ->with('opponentTeam:id,name')
            ->get()
            ->map(fn(Scrim $scrim) => [
                'title' => 'Scrim vs ' . $scrim->opponentTeam->name,
                'start' => $scrim->scheduled_at->toIso8601String(),
                'url' => route('scrims.show', [
                    'slug' => currentTeam()->slug,
                    'id' => $scrim->id,
                    
                ]),
                'backgroundColor' => '#D4AF37',
                'extendedProps' => ['type' => 'scrim'],
            ])
            ->all();
        $absences = Absence::query()
            ->whereHas('teamMember', fn($q) => $q->where('team_id', currentTeam()->id))
            ->with('teamMember.user:id,username')
            ->get()
            ->map(fn(Absence $absence) => [
                'title' => $absence->teamMember->user->username . ' — ' . $absence->justification->value,
                'start' => $absence->date->format('Y-m-d'),
                'allDay' => true,
                'backgroundColor' => '#EF4444',
                'extendedProps' => [
                    'type' => 'absence',
                    'justification' => $absence->justification->label(),
                ],
            ]);
        $this->events = collect($scrims)->concat(collect($absences))->values()->all();

    }
};
?>

<div>
    <section class="flex flex-col gap-6">
        <h2 class="text-[32px] font-bold">
            {{ __('pages/calendar/index.title') }}
        </h2>
        <div id="calendar" data-events='@json($events)'></div>
    </section>
</div>