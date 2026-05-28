<?php

namespace App\Policies;

use App\Enums\StatusApplication;
use App\Enums\StatusInTeam;
use App\Enums\StatusInvitation;
use App\Models\Team;
use App\Models\TeamApplication;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TeamInvitationPolicy
{
    public function create(User $user, Team $team, User $invitedUser): Response
    {
        if ($user->current_team_id !== $team->id) {
            return Response::deny(__('policies/roster.error_not_current_team'));
        }
        if (! $user->canManageCurrentTeam()) {
            return Response::deny(__('policies/roster.error_manage_team'));
        }

        if ($this->hasPendingSend($invitedUser->id, $team->id)) {
            return Response::deny(__('policies/roster.error_pending_send'));
        }
        if ($this->isAlreadyMember($invitedUser, $team)) {
            return Response::deny(__('policies/roster.error_already_member'));
        }
        if ($this->hasAlreadyApplied($invitedUser, $team)) {
            return Response::deny(__('policies/roster.error_already_applied'));
        }

        return Response::allow();
    }

    private function hasPendingSend(int $userId, int $teamId): bool
    {
        return TeamInvitation::query()
            ->where('user_id', $userId)
            ->where('team_id', $teamId)
            ->where('status', StatusInvitation::PENDING)
            ->exists();
    }

    private function isAlreadyMember(User $user, Team $team): bool
    {
        return $team->members()
            ->where('user_id', $user->id)
            ->where('team_id', $team->id)
            ->wherePivot('status', StatusInTeam::ACCEPTED)
            ->exists();
    }

    private function hasAlreadyApplied(User $user, Team $team): bool
    {
        return TeamApplication::query()
            ->where('user_id', $user->id)
            ->where('team_id', $team->id)
            ->where('status', StatusApplication::PENDING)
            ->exists();
    }
}
