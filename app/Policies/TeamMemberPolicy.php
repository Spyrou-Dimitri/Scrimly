<?php

namespace App\Policies;

use App\Models\TeamMember;
use App\Models\User;

class TeamMemberPolicy
{
    public function manageAvailability(User $user, TeamMember $teamMember): bool
    {
        $member = currentMember();

        if ($member === null) {
            return false;
        }

        return $member->id === $teamMember->id;
    }
}
