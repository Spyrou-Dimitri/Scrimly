<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

new #[Layout('layouts::team')] class extends Component
{
    public function render()
    {
        return $this->view()->title(__('layouts/team.nav.dashboard'));
    }
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