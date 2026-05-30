<?php

use Illuminate\Support\Facades\Broadcast;
use App\Enums\StatusInTeam;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.{teamId}', function ($user, int $teamId) {
    return $user->current_team_id === $teamId
        && $user->teams()
        ->where('teams.id', $teamId)
        ->wherePivot('status', StatusInTeam::ACCEPTED)
        ->exists();;
});

