<?php

use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

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
