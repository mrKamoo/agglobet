<?php

namespace Database\Factories;

use App\Models\Game;
use App\Models\Season;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

class GameFactory extends Factory
{
    protected $model = Game::class;

    public function definition(): array
    {
        return [
            'season_id' => Season::factory(),
            'home_team_id' => Team::factory(),
            'away_team_id' => Team::factory(),
            'matchday' => fake()->numberBetween(1, 38),
            'match_date' => fake()->dateTimeBetween('now', '+3 months'),
            'home_score' => null,
            'away_score' => null,
            'is_finished' => false,
        ];
    }

    public function finished(): static
    {
        return $this->state(fn (array $attributes) => [
            'home_score' => fake()->numberBetween(0, 5),
            'away_score' => fake()->numberBetween(0, 5),
            'is_finished' => true,
            'match_date' => fake()->dateTimeBetween('-2 months', '-1 day'),
        ]);
    }

    public function upcoming(): static
    {
        return $this->state(fn (array $attributes) => [
            'home_score' => null,
            'away_score' => null,
            'is_finished' => false,
            'match_date' => fake()->dateTimeBetween('+1 day', '+3 months'),
        ]);
    }
}
