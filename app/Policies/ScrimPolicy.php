<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Scrim;
use App\Models\TeamMember;

class ScrimPolicy
{
    /**
     * Create a new policy instance.
     */
    public function create(User $user): bool
    {
        if (!$user->current_team_id) {
            return false;
        }
        $teamMember = TeamMember::query()->where('team_id', $user->current_team_id)->where('user_id', $user->id)->first();
        if ($teamMember === null || !$teamMember->isCoachOrStaff()) {
            return false;
        }
        return true;
    }
    public function edit(User $user): bool
    {
        if (!$user->current_team_id) {
            return false;
        }
        $teamMember = TeamMember::query()->where('team_id', $user->current_team_id)->where('user_id', $user->id)->first();
        if ($teamMember === null || !$teamMember->isCoachOrStaff()) {
            return false;
        }
        return true;
    }
}
