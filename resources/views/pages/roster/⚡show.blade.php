<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\TeamMember;
new #[Layout('layouts::team')] class extends Component
{
    public TeamMember $teamMember;
    public function mount($id)
    {
        $this->teamMember = TeamMember::find($id);
    }
};
?>

<div>
    
</div>