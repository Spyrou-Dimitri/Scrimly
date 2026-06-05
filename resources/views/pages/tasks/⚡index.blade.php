<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use App\Enums\StatusTask;
use Illuminate\Support\Facades\Gate;

new #[Layout('layouts::team')] class extends Component
{
    public function render()
    {
        $title = Gate::allows('manageTeam', User::class)
            ? __('pages/tasks/index.coach_title')
            : __('pages/tasks/index.player_title');
            
        return $this->view()->title($title);
    }
};
?>

<div>
    @php
    $canManageTeam = Gate::allows('manageTeam', User::class);
    @endphp
    @if ($canManageTeam)
    <livewire:tasks-views::coach />
    @else
    @php
    $playerColumnOrder = [\App\Enums\StatusTask::DONE, \App\Enums\StatusTask::IN_PROGRESS, \App\Enums\StatusTask::TODO];
    @endphp
    <livewire:tasks-views::player />
    @endif

</div>