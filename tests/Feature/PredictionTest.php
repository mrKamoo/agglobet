<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Game;
use App\Models\Season;
use App\Models\Team;
use App\Models\Prediction;
use App\Models\PointsRule;
use App\Services\FootballDataService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PredictionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create default points rule
        PointsRule::create([
            'name' => 'Règle standard',
            'description' => 'Système de points standard',
            'exact_score' => 5,
            'correct_difference' => 3,
            'correct_winner' => 1,
            'is_active' => true,
        ]);
    }

    public function test_user_can_create_prediction_before_match_starts()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $game = Game::factory()->create([
            'match_date' => now()->addDay(),
            'is_finished' => false,
        ]);

        $response = $this->actingAs($user)->post(route('predictions.store', $game), [
            'home_score' => 2,
            'away_score' => 1,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('predictions', [
            'user_id' => $user->id,
            'game_id' => $game->id,
            'home_score' => 2,
            'away_score' => 1,
        ]);
    }

    public function test_cannot_predict_after_match_starts()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $game = Game::factory()->create([
            'match_date' => now()->subHour(), // Match déjà commencé
            'is_finished' => false,
        ]);

        $response = $this->actingAs($user)->post(route('predictions.store', $game), [
            'home_score' => 2,
            'away_score' => 1,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('predictions', [
            'user_id' => $user->id,
            'game_id' => $game->id,
        ]);
    }

    public function test_prediction_calculates_correct_points_for_exact_score()
    {
        $game = Game::factory()->create([
            'home_score' => 2,
            'away_score' => 1,
            'is_finished' => true,
        ]);

        $prediction = Prediction::factory()->create([
            'game_id' => $game->id,
            'home_score' => 2,
            'away_score' => 1,
        ]);

        $service = app(FootballDataService::class);
        $service->calculatePoints($game);

        $prediction->refresh();
        $this->assertEquals(5, $prediction->points_earned);
    }
}