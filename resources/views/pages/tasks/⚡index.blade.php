<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;
use App\Enums\StatusTask;

new #[Layout('layouts::team')] class extends Component
{
    
};
?>

<div>
    @can('manageTeam', \App\Models\User::class)
    <livewire:tasks-views::coach />
    @else
        @php
            $playerColumnOrder = [\App\Enums\StatusTask::DONE, \App\Enums\StatusTask::IN_PROGRESS, \App\Enums\StatusTask::TODO];
        @endphp¨
        <livewire:tasks-views::player />
    @endcan

</div>
