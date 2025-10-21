<?php

namespace Database\Seeders;

use App\Models\PointsRule;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PointsRuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PointsRule::create([
            'name' => 'Règle standard',
            'description' => 'Système de points standard pour les pronostics',
            'exact_score' => 5,
            'correct_difference' => 3,
            'correct_winner' => 1,
            'is_active' => true,
        ]);
    }
}
