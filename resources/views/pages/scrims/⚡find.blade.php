<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use App\Models\Team;

new #[Layout('layouts::team')] class extends Component
{

    public string $term = '';
    #[Computed]
    public function teams()
    {
        $teams = Team::query()->with('members')->where('id', '!=', currentTeam()->id);

        if ($this->term !== '') {
            $teams->where('name', 'like', '%'.$this->term.'%');
        }
        if ($this->server !== '') {
            $teams->where('server', $this->server);
        }
        if ($this->goal !== '') {
            $teams->where('goal', $this->goal);
        }

        return $teams->orderBy('name', 'asc')->paginate(8);
    }
};
?>

<div>
    {{ dd($this->teams) }}
    Oui c moi
</div>