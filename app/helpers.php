<?php

use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\TeamMember;

if (! function_exists('currentTeam')) {

    function currentTeam(): ?Team
    {
        return once(function () {
            /** @var User|null $user */
            $user = Auth::user();

            return $user?->currentTeam;
        });
    }
}

if (! function_exists('currentMember')) {
    function currentMember(): ?TeamMember
    {
        return once(function () {
            return TeamMember::where('user_id', Auth::user()->id)
                ->where('team_id', currentTeam()->id)
                ->first();
        });
    }
}
