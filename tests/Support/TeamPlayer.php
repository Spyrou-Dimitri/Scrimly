<?php

namespace Tests\Support;

use App\Enums\RoleInGame;
use App\Enums\RoleInTeam;
use App\Enums\StatusInTeam;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;

final class TeamPlayer
{
    public function __construct(
        public User $user,
        public Team $team,
        public TeamMember $member,
    ) {}

    public static function create(): TeamPlayer
    {
        $creator = User::factory()->create();

        $team = Team::factory()->create([
            'creator_id' => $creator->id,
        ]);

        $user = User::factory()->create([
            'current_team_id' => $team->id,
        ]);

        $member = TeamMember::create([
            'team_id' => $team->id,
            'user_id' => $user->id,
            'roleInTeam' => RoleInTeam::PLAYER,
            'roleInGame' => RoleInGame::MID,
            'is_starter' => true,
            'status' => StatusInTeam::ACCEPTED,
            'joined_at' => now(),
        ]);

        return new TeamPlayer($user, $team, $member);
    }
}
