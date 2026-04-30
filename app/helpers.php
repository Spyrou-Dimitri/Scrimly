<?php

use App\Models\Team;
use Illuminate\Support\Facades\Auth;

if (! function_exists('currentTeam')) {

    function currentTeam(): ?Team
    {
        $user = Auth::user();

        if (! $user) {
            return null;
        }

        return $user->currentTeam;
    }
}
