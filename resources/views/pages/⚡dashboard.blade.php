<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\TeamMember;

new #[Layout('layouts::team')] class extends Component
{
    
};
?>

<div>
    @can('manageTeam', \App\Models\User::class)
    <livewire:dashboard-views::coach />
    @else
        <livewire:dashboard-views::player />
    @endcan

</div>