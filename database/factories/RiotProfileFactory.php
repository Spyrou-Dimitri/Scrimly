<?php

namespace Database\Factories;

use App\Models\RiotProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RiotProfile>
 */
class RiotProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'riot_tag' => null,
            'riot_puuid' => null,
            'tier' => null,
            'rank' => null,
            'lp' => null,
        ];
    }
}
