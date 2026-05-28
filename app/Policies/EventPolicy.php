<?php

namespace App\Policies;

use App\Models\User;

class Event
{
    /**
     * Create a new policy instance.
     */
    public function create(User $user): bool
}
