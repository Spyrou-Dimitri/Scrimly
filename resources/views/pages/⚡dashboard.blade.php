<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

new #[Layout('layouts::team')] class extends Component
{
    
};
?>

<div>
    @php
    $canManageTeam = Gate::allows('manageTeam', User::class);
    @endphp
    @if ($canManageTeam)
    <livewire:dashboard-views::coach />
    @else
        <livewire:dashboard-views::player />
    @endif

</div>