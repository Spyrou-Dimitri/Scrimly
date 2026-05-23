<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function manageOnlyOurTasks(User $user, Task $task): bool
    {
        $member = currentMember();

        if ($member === null) {
            return false;
        }

        return $task->team_member_id === $member->id;
    }
}
