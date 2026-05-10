<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;
use App\Enums\StatusTask;
use Illuminate\Support\Facades\Auth;

new #[Layout('layouts::team')] class extends Component
{
    
};
?>

<div>
    @if (currentTeam()->creator_id === Auth::user()->id || currentMember()->isCoachOrStaff())
    <livewire:tasks-views::coach />
    @else
        @php
            $playerColumnOrder = [\App\Enums\StatusTask::DONE, \App\Enums\StatusTask::IN_PROGRESS, \App\Enums\StatusTask::TODO];
        @endphp
        <livewire:tasks-views::player />
    @endif

</div>
