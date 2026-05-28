<?php

namespace Database\Factories;

use App\Enums\RoleInGame;
use App\Enums\RoleInTeam;
use App\Enums\StatusInvitation;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TeamInvitation>
 */
class TeamInvitationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'status' => StatusInvitation::PENDING,
            'roleInTeam' => RoleInTeam::PLAYER,
            'roleInGame' => RoleInGame::MID,
            'motivation' => fake()->sentence(),
            'user_id' => User::factory(),
            'team_id' => Team::factory(),
        ];
    }
}
