<?php

namespace App\Observers;

use App\Models\TeamMember;
use App\Enums\RoleInTeam;

class TeamMemberObserver
{
    /**
     * Handle the TeamMember "created" event.
     */
    public function created(TeamMember $teamMember): void
    {
        if ($teamMember->is_starter && $teamMember->roleInTeam === RoleInTeam::PLAYER) {
            TeamMember::where('team_id', $teamMember->team_id)
                ->where('roleInGame', $teamMember->roleInGame)
                ->where('is_starter', true)
                ->where('id', '!=', $teamMember->id)
                ->update(['is_starter' => false]);
        }
    }

    /**
     * Handle the TeamMember "updated" event.
     */
    public function updated(TeamMember $teamMember): void
    {
        //
    }

    /**
     * Handle the TeamMember "deleted" event.
     */
    public function deleted(TeamMember $teamMember): void
    {
        //
    }

    /**
     * Handle the TeamMember "restored" event.
     */
    public function restored(TeamMember $teamMember): void
    {
        //
    }

    /**
     * Handle the TeamMember "force deleted" event.
     */
    public function forceDeleted(TeamMember $teamMember): void
    {
        //
    }
}
