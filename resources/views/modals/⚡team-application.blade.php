<?php

use Livewire\Component;
use App\Models\TeamApplication;

new class extends Component
{
    public TeamApplication $candidate;
    public function mount($model_id)
    {
        $this->candidate = TeamApplication::find($model_id);
    }
};
?>

<div>
    <x-layout.head-modal :title="__('modals/team-application.title') . ' ' . $this->candidate->user->username">
        <h1>BONJOUR</h1>
        
    </x-layout.head-modal>
</div>