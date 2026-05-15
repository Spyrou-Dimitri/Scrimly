<?php

use Livewire\Component;
use Livewire\Attributes\Layout;

new #[Layout('layouts::team')] class extends Component
{
    
};
?>

<div>
    <section>
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold">
                {{ __('pages/scrims/index.title') }}
            </h2>
            <x-cta :href="route('scrims.find', ['slug' => currentTeam()->slug])" :title="__('pages/scrims/index.create_scrim')" :class="'cta-primary'">
                {{ __('pages/scrims/index.create_scrim') }}
            </x-cta>
        </div>

    </section>
</div>