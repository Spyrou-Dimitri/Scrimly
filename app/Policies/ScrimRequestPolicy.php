<?php

namespace App\Policies;

use App\Enums\StatusScrimRequest;
use App\Models\ScrimRequest;
use App\Models\Team;
use App\Models\User;

class ScrimRequestPolicy
{
    public function create(User $user, Team $receiverTeam): bool
    {
        if (! $user->canManageCurrentTeam()) {
            return false;
        }

        $requesterTeamId = $user->current_team_id;

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

    public function delete(User $user): bool
    {
        return $user->canManageCurrentTeam();
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
