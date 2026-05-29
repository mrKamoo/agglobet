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

    public function test_user_can_predict_champion_before_deadline()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $season = Season::factory()->create([
            'name' => 'World Cup 2026',
            'is_active' => true,
            'type' => 'tournament',
        ]);
        $team = Team::factory()->create();

        // Travel to before the deadline (e.g. 2026-05-17)
        $this->travelTo(\Carbon\Carbon::create(2026, 5, 17, 12, 0, 0));

        $response = $this->actingAs($user)->post(route('predictions.champion.store', $season), [
            'team_id' => $team->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('champion_predictions', [
            'user_id' => $user->id,
            'season_id' => $season->id,
            'team_id' => $team->id,
        ]);
    }

    public function test_user_cannot_predict_champion_after_deadline()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $season = Season::factory()->create([
            'name' => 'World Cup 2026',
            'is_active' => true,
            'type' => 'tournament',
        ]);
        $team = Team::factory()->create();

        // Travel to after the deadline (e.g. 2026-06-21)
        $this->travelTo(\Carbon\Carbon::create(2026, 6, 21, 12, 0, 0));

        $response = $this->actingAs($user)->post(route('predictions.champion.store', $season), [
            'team_id' => $team->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('champion_predictions', [
            'user_id' => $user->id,
            'season_id' => $season->id,
        ]);
    }

    public function test_user_cannot_change_champion_prediction()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $season = Season::factory()->create([
            'name' => 'World Cup 2026',
            'is_active' => true,
            'type' => 'tournament',
        ]);
        $team1 = Team::factory()->create();
        $team2 = Team::factory()->create();

        // Create initial prediction
        \App\Models\ChampionPrediction::create([
            'user_id' => $user->id,
            'season_id' => $season->id,
            'team_id' => $team1->id,
        ]);

        // Travel to before the deadline
        $this->travelTo(\Carbon\Carbon::create(2026, 5, 17, 12, 0, 0));

        // Attempt to change choice
        $response = $this->actingAs($user)->post(route('predictions.champion.store', $season), [
            'team_id' => $team2->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('champion_predictions', [
            'user_id' => $user->id,
            'season_id' => $season->id,
            'team_id' => $team1->id,
        ]);
        $this->assertDatabaseMissing('champion_predictions', [
            'user_id' => $user->id,
            'season_id' => $season->id,
            'team_id' => $team2->id,
        ]);
    }

    public function test_leaderboard_includes_30_bonus_points_when_champion_is_correct()
    {
        $user = User::factory()->create(['email_verified_at' => now(), 'exclude_from_leaderboard' => false]);
        $team = Team::factory()->create();
        
        $season = Season::factory()->create([
            'name' => 'World Cup 2026',
            'is_active' => true,
            'type' => 'tournament',
            'winner_team_id' => $team->id,
        ]);

        \App\Models\ChampionPrediction::create([
            'user_id' => $user->id,
            'season_id' => $season->id,
            'team_id' => $team->id,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard', ['season_id' => $season->id]));

        // Load leaderboard index to see calculated points
        $response = $this->actingAs($user)->get(route('dashboard', ['season_id' => $season->id]));

        // Check using Controller instance or simple DB assertions to ensure total_points is correct
        $leaderboardController = new \App\Http\Controllers\LeaderboardController();
        $request = new \Illuminate\Http\Request(['season_id' => $season->id]);
        
        $view = $leaderboardController->index($request);
        $leaderboardData = $view->getData()['leaderboard'];

        $userRow = collect($leaderboardData)->firstWhere('id', $user->id);
        $this->assertNotNull($userRow);
        $this->assertEquals(30, $userRow->total_points);
    }

    public function test_world_cup_standings_page_renders_with_champion_prediction_widget()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $season = Season::factory()->create([
            'name' => 'World Cup 2026',
            'is_active' => true,
            'type' => 'tournament',
        ]);
        $team = Team::factory()->create();

        // Create a game so team is in homeGames/awayGames
        Game::factory()->create([
            'season_id' => $season->id,
            'home_team_id' => $team->id,
            'match_date' => now()->addDay(),
            'is_finished' => false,
            'round' => 'Phase de groupes',
        ]);

        $response = $this->actingAs($user)->get(route('worldcup.index', $season));

        $response->assertStatus(200);
        $response->assertViewHas('wcTeams');
        $response->assertViewHas('userChampionPrediction');
        $response->assertViewHas('championDeadlinePassed');
    }
}