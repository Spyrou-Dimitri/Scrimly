<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function manageTeam(User $user): bool
    {
        return $user->canManageCurrentTeam();
    }
}
