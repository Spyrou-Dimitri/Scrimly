<?php

namespace App\Policies;

use App\Enums\StatusInvitation;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;

class TeamInvitationPolicy
{
    public function create(User $user, Team $team, User $invitedUser): bool
    {
        if (! $user->canManageCurrentTeam()) {
            return false;
        }

        if ($this->hasPendingSend($invitedUser->id, $team->id)) {
            return false;
        }

        return true;
    }

    private function hasPendingSend(int $userId, int $teamId): bool
    {
        return TeamInvitation::query()
            ->where('user_id', $userId)
            ->where('team_id', $teamId)
            ->where('status', StatusInvitation::PENDING)
            ->exists();
    }
}
