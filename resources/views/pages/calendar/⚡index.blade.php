<?php

use Livewire\Component;
use Livewire\Attributes\Layout;

new #[Layout('layouts::team')] class extends Component
{
    //
};
?>

<div>
    <section class="flex flex-col gap-6">
        <h2 class="text-[32px] font-bold">
            {{ __('pages/calendar/index.title') }}
        </h2>
        <div id="calendar"></div>
    </section>
</div>