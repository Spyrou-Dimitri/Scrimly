<?php

use Livewire\Component;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\Scrim;
use App\Enums\StatusScrim;
use Livewire\Attributes\Computed;
use App\Models\ScrimGamePlayer;

new class extends Component
{
    public bool $statsScrim = true;
    public Team $team;
    public TeamMember $member;
    public function mount(): void
    {
        if (! currentMember()) {
            abort(403);
        }
        $this->team = currentTeam();
        $this->member = currentMember();
    }
    #[Computed]
    public function averageKda(): float
    {
        if ($this->statsScrim) {
            return $this->member->overallScrimKda($this->team->id);
        }
        return $this->member->user->riotProfile->getGeneralKda();
    }
    #[Computed]
    public function winrate(): float
    {
        if ($this->statsScrim) {
            return $this->team->overallScrimWinrate();
        } else {
            return $this->member->user->riotProfile->getWinratePercentage();
        }
    }
    #[Computed]
    public function favoriteChampion(): ?string
    {
        if ($this->statsScrim) {
            return $this->member->favoriteChampionScrim();
        } else {
            return $this->member->user->riotProfile->favoriteChampion();
        }
    }
    #[Computed]
    public function totalGames(): int
    {
        if ($this->statsScrim) {
            return $this->team->scrims()
                ->whereIn('status', [StatusScrim::COMPLETED, StatusScrim::ABORTED])
                ->count();
        } else {
            return $this->member->user?->riotProfile?->totalGames() ?? 0;
        }
    }
};
?>

<div>
    <h1>{{ $this->statsScrim ? 'Average KDA (Scrim)' : 'Average KDA (Game)' }}</h1>
    <p>{{ $this->averageKda }}</p>
    <h1>{{ $this->statsScrim ? 'Winrate (Scrim)' : 'Winrate (Game)' }}</h1>
    <p>{{ $this->winrate }}</p>
</div>