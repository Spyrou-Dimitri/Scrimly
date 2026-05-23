<?php

namespace App\Policies;

use App\Enums\StatusInTeam;
use App\Models\TeamApplication;
use App\Models\TeamMember;
use App\Models\User;

class TeamApplicationPolicy
{
    public function view(User $user, TeamApplication $teamApplication): bool
    {
        if ($user->current_team_id !== $teamApplication->team_id) {
            return false;
        }

        return TeamMember::query()
            ->where('user_id', $user->id)
            ->where('team_id', $user->current_team_id)
            ->where('status', StatusInTeam::ACCEPTED)
            ->exists();
    }
}
