<?php

namespace App\Observers;

use App\Enums\RoleInTeam;
use App\Enums\StatusInTeam;
use App\Models\TeamMember;

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
        if ($teamMember->is_starter && $teamMember->roleInTeam === RoleInTeam::PLAYER && $teamMember->wasChanged('is_starter')) {
            TeamMember::where('team_id', $teamMember->team_id)
                ->where('roleInGame', $teamMember->roleInGame)
                ->where('is_starter', true)
                ->where('id', '!=', $teamMember->id)
                ->update(['is_starter' => false]);
        }

        if ($teamMember->status === StatusInTeam::REJECTED && $teamMember->wasChanged('status')) {
            $user = $teamMember->user;
            if ($user->current_team_id === $teamMember->team_id) {
                $user->update(['current_team_id' => null]);
            }
        }
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
