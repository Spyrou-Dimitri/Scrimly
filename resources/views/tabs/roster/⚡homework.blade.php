<?php

use App\Models\TeamMember;
use Livewire\Attributes\Locked;
use Livewire\Component;

new class extends Component
{
    #[Locked]
    public TeamMember $teamMember;

    public function mount(TeamMember $teamMember): void
    {
        $this->teamMember = $teamMember;
    }
}; ?>

<section class="flex flex-col gap-4" aria-labelledby="roster-homework-heading">
    <h3 id="roster-homework-heading" class="font-spaceGrotesk text-2xl font-bold text-white">
        {{ __('pages/roster/show.tabs.homework') }}
    </h3>

    <p class="text-base text-text-gray">{{ __('pages/roster/show.homework.empty') }}</p>
</section>
