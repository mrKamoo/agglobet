<?php

namespace Database\Factories;

use App\Models\Game;
use App\Models\Prediction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PredictionFactory extends Factory
{
    protected $model = Prediction::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'game_id' => Game::factory(),
            'home_score' => fake()->numberBetween(0, 5),
            'away_score' => fake()->numberBetween(0, 5),
            'points_earned' => 0,
        ];
    }

    public function withPoints(int $points): static
    {
        return $this->state(fn (array $attributes) => [
            'points_earned' => $points,
        ]);
    }
}
