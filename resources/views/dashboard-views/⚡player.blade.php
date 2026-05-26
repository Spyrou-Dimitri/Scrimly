<?php

use Livewire\Component;
use App\Models\Team;

new class extends Component
{
    public Team $team;
    public function mount(): void
    {
        if (! currentMember()) {
            abort(403);
        }
        $this->team = currentTeam();
    }
};
?>

<div>
    
</div>