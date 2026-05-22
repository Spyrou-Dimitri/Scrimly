<?php

namespace App\Policies;

use App\Enums\StatusScrimRequest;
use App\Models\ScrimRequest;
use App\Models\Team;
use App\Models\User;
use App\Models\TeamMember;

class ScrimRequestPolicy
{
    public function create(User $user, Team $receiverTeam): bool
    {
        $requesterTeamId = $user->current_team_id;

        if ($requesterTeamId === null) {
            return false;
        }
        if (!$user->current_team_id) {
            return false;
        }
        $teamMember = TeamMember::query()->where('team_id', $requesterTeamId)->where('user_id', $user->id)->first();

        if ($teamMember === null || !$teamMember->isCoachOrStaff()) {
            return false;
        }

        if ($requesterTeamId === null || $requesterTeamId === $receiverTeam->id) {
            return false;
        }

        if ($this->hasPendingSend($requesterTeamId, $receiverTeam->id)) {
            return false;
        }

        if ($this->hasPendingReceive($requesterTeamId, $receiverTeam->id)) {
            return false;
        }

        return true;
    }

    private function hasPendingSend(int $requesterTeamId, int $receiverTeamId): bool
    {
        return ScrimRequest::query()
            ->where('receiver_team_id', $receiverTeamId)
            ->where('requester_team_id', $requesterTeamId)
            ->where('status', StatusScrimRequest::PENDING)
            ->exists();
    }

    private function hasPendingReceive(int $requesterTeamId, int $receiverTeamId): bool
    {
        return ScrimRequest::query()
            ->where('requester_team_id', $receiverTeamId)
            ->where('receiver_team_id', $requesterTeamId)
            ->where('status', StatusScrimRequest::PENDING)
            ->exists();
    }
}
