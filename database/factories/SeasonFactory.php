<?php

namespace Database\Factories;

use App\Models\Season;
use Illuminate\Database\Eloquent\Factories\Factory;

class SeasonFactory extends Factory
{
    protected $model = Season::class;

    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('-1 year', 'now');
        $endDate = (clone $startDate)->modify('+9 months');

        return [
            'name' => fake()->unique()->year() . '-' . (fake()->year() + 1),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'is_active' => false,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }
}
