<?php

namespace App\Policies;

use App\Enums\StatusApplication;
use App\Enums\StatusInTeam;
use App\Models\Team;
use App\Models\TeamApplication;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Auth\Access\Response;

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

    public function canApplyForTeam(User $user, Team $team): Response
    {
        if ($team->members()
            ->where('user_id', $user->id)
            ->wherePivot('status', StatusInTeam::ACCEPTED)
            ->exists()) {
            return Response::deny(__('policies/roster.error_apply_already_member'));
        }

        if ($team->teamApplications()
            ->where('user_id', $user->id)
            ->where('status', StatusApplication::PENDING)
            ->exists()) {
            return Response::deny(__('policies/roster.error_apply_pending_application'));
        }

        return Response::allow();
    }
}
