<?php

namespace Database\Factories;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Team>
 */
class TeamFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company(),
            'slug' => fake()->unique()->slug(2),
            'tag' => strtoupper(fake()->unique()->lexify('????')),
            'logo' => null,
            'description' => fake()->sentence(),
            'language' => 'fr',
            'server' => 'EUW',
            'goal' => 'fun',
            'creator_id' => User::factory(),
        ];
    }
}
