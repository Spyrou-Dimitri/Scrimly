<?php

use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

if (! function_exists('currentTeam')) {

    function currentTeam(): ?Team
    {
        return once(function () {
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
if (! function_exists('getChampionsList')) {
    function getChampionsList(): array
    {
        return once(function () {
            return json_decode(file_get_contents(resource_path('data/champions.json')), true);
        });
    }
}
