<?php

use Illuminate\Support\Facades\Broadcast;
use App\Enums\StatusInTeam;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
Broadcast::channel('presence.{teamId}', function ($user, int $teamId) {
    $isMember = $user->current_team_id === $teamId
        && $user->teams()
            ->where('teams.id', $teamId)
            ->wherePivot('status', StatusInTeam::ACCEPTED)
            ->exists();

    if (! $isMember) {
        return null;
    }
    return [
        'id' => $user->id,
        'username' => $user->username,
    ];
});

Broadcast::channel('chat.{teamId}', function ($user, int $teamId) {
    $isMember = $user->current_team_id === $teamId
        && $user->teams()
            ->where('teams.id', $teamId)
            ->wherePivot('status', StatusInTeam::ACCEPTED)
            ->exists();

    if (! $isMember) {
        return null;
    }
    
    return [
        'id' => $user->id,
        'username' => $user->username,
        'avatar_url' => $user->avatar_url,
    ];
});

