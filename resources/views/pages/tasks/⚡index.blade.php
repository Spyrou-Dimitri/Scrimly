<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;


new #[Layout('layouts::team')] class extends Component
{
    #[Computed]
    public function allTasks(): Collection  
    {
        return Task::where('team_id', currentTeam()->id)->get();
    }

};
?>

<div>
    <section class="flex flex-col gap-4">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold">
                {{ __('pages/tasks/index.coach_title') }}
            </h2>
            <x-cta :href="route('tasks.create', ['slug' => currentTeam()->slug])" :title="__('pages/tasks/index.coach_create_task_title')" :class="'cta-primary'">
                {{ __('pages/tasks/index.coach_create_task_button') }}
            </x-cta>
        </div>
        <table class="w-full shadow-basic">
            <thead class="bg-[#0D0E12]">
                <tr class="">
                    <th class="text-left p-6 ">Devoir</th>
                    <th class="text-left p-6">Assigné à</th>
                    <th class="text-left p-6">Statut</th>
                    <th class="text-left p-6">Progression</th>
                    <th class="text-left p-6">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-bg-widget">
                @foreach ($this->allTasks as $task)
                    <tr>
                        <td class="p-6"><p class="block truncate font-bold">{{ $task->title }} </p> <p class="text-xs font-bold text-text-secondary">Echéance : {{ $task->deadline->format('d/m/Y') }}</p></td>
                        <td class="p-6">{{ $task->teamMember->user->username }}</td>
                        <td class="p-6"> <span class="{{ $task->status->macaron() }}">{{ $task->status->label() }}</span></td>
                        <td class="p-6">{{ $task->subtasks->sum('progression') / $task->subtasks->count() * 100 }}%</td>
                        <td class="p-6">#</td>
                    </tr>
                @endforeach

        </table>
    </section>
</div>