<?php

use Livewire\Component;
use Livewire\Attributes\Layout;


new #[Layout('layouts::team')] class extends Component
{
    
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
    </section>
</div>