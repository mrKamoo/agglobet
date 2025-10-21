<?php

namespace Database\Factories;

use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

class TeamFactory extends Factory
{
    protected $model = Team::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(3, true) . ' FC',
            'short_name' => fake()->unique()->lexify('???'),
            'logo' => fake()->imageUrl(100, 100, 'sports', true, 'team'),
            'city' => fake()->city(),
            'stadium' => fake()->company() . ' Stadium',
        ];
    }
}
