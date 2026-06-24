<?php

namespace App\Http\Controllers;

use App\Models\ChampionPrediction;
use App\Models\User;
use App\Models\Game;
use App\Models\Prediction;
use App\Models\PointsRule;
use App\Models\Season;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LeaderboardController extends Controller
{
    public function index(Request $request)
    {
        $seasons = Season::orderBy('start_date', 'desc')->get();
        $selectedSeasonId = $request->get('season_id', Season::where('is_active', true)->first()?->id ?? $seasons->first()?->id);
        
        $period = $request->get('period', 'all'); // all, month, week, matchday
        $matchday = $request->get('matchday');

        // Get points rules for calculation
        $pointsRule = PointsRule::where('is_active', true)->first();

        // Build base query with detailed statistics
        $query = User::query()
            ->select('users.id', 'users.name', 'users.email', 'users.avatar')
            ->where('users.exclude_from_leaderboard', false);

        // Apply period filters
        $predictionsQuery = Prediction::query()
            ->join('games', 'predictions.game_id', '=', 'games.id')
            ->where('games.is_finished', true);

        if ($selectedSeasonId) {
            $predictionsQuery->where('games.season_id', $selectedSeasonId);
        }

        switch ($period) {
            case 'month':
                $predictionsQuery->whereMonth('games.match_date', Carbon::now()->month)
                    ->whereYear('games.match_date', Carbon::now()->year);
                break;
            case 'week':
                $predictionsQuery->whereBetween('games.match_date', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek()
                ]);
                break;
            case 'matchday':
                if ($matchday) {
                    $predictionsQuery->where('games.matchday', $matchday);
                }
                break;
        }

        $selectedSeason = Season::find($selectedSeasonId);

        // Preload all champion predictions for this season indexed by user_id
        $championPredictions = $selectedSeasonId
            ? ChampionPrediction::with('team')->where('season_id', $selectedSeasonId)->get()->keyBy('user_id')
            : collect();

        // Get detailed statistics for each user
        $leaderboard = $query->get()->map(function ($user) use ($predictionsQuery, $pointsRule, $selectedSeasonId, $selectedSeason, $championPredictions) {
            $userPredictions = (clone $predictionsQuery)
                ->where('predictions.user_id', $user->id)
                ->get();

            $totalPoints = $userPredictions->sum('points_earned');

            $cp = $championPredictions->get($user->id);
            if ($cp && $selectedSeason && $cp->isCorrect($selectedSeason)) {
                $totalPoints += ChampionPrediction::BONUS_POINTS;
            }

            $predictionsCount = $userPredictions->count();

            $exactScores = $userPredictions->where('points_earned', $pointsRule?->exact_score ?? 5)->count();
            $correctDifferences = $userPredictions->where('points_earned', $pointsRule?->correct_difference ?? 3)->count();
            $correctWinners = $userPredictions->where('points_earned', $pointsRule?->correct_winner ?? 1)->count();
            $incorrectPredictions = $userPredictions->where('points_earned', 0)->count();

            $successfulPredictions = $predictionsCount - $incorrectPredictions;
            $successRate = $predictionsCount > 0 ? round(($successfulPredictions / $predictionsCount) * 100, 1) : 0;
            $avgPoints = $predictionsCount > 0 ? round($totalPoints / $predictionsCount, 2) : 0;

            // Calculate current streak
            $currentStreak = $this->calculateCurrentStreak($user->id, $selectedSeasonId);
            $bestStreak = $this->calculateBestStreak($user->id, $selectedSeasonId);

            return (object) [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar_url' => $user->avatar_url,
                'total_points' => $totalPoints,
                'predictions_count' => $predictionsCount,
                'exact_scores' => $exactScores,
                'correct_differences' => $correctDifferences,
                'correct_winners' => $correctWinners,
                'incorrect_predictions' => $incorrectPredictions,
                'success_rate' => $successRate,
                'avg_points' => $avgPoints,
                'current_streak' => $currentStreak,
                'best_streak' => $bestStreak,
                'champion_team' => $cp?->team,
            ];
        })->sortByDesc('total_points')->values();

        // Get available matchdays for filter
        $matchdays = Game::when($selectedSeasonId, function ($q) use ($selectedSeasonId) {
                $q->where('season_id', $selectedSeasonId);
            })
            ->distinct()
            ->orderBy('matchday')
            ->pluck('matchday');

        // Calculate period leaders
        $weekLeader = $this->getPeriodLeader('week', $selectedSeasonId);
        $monthLeader = $this->getPeriodLeader('month', $selectedSeasonId);

        return view('leaderboard.index', compact(
            'leaderboard',
            'seasons',
            'selectedSeasonId',
            'period',
            'matchday',
            'matchdays',
            'weekLeader',
            'monthLeader',
            'pointsRule'
        ));
    }

    private function calculateCurrentStreak($userId, $seasonId = null)
    {
        $query = Prediction::query()
            ->join('games', 'predictions.game_id', '=', 'games.id')
            ->where('predictions.user_id', $userId)
            ->where('games.is_finished', true);

        if ($seasonId) {
            $query->where('games.season_id', $seasonId);
        }

        $predictions = $query->orderByDesc('games.match_date')
            ->select('predictions.points_earned')
            ->get();

        $streak = 0;
        foreach ($predictions as $prediction) {
            if ($prediction->points_earned > 0) {
                $streak++;
            } else {
                break;
            }
        }

        return $streak;
    }

    private function calculateBestStreak($userId, $seasonId = null)
    {
        $query = Prediction::query()
            ->join('games', 'predictions.game_id', '=', 'games.id')
            ->where('predictions.user_id', $userId)
            ->where('games.is_finished', true);

        if ($seasonId) {
            $query->where('games.season_id', $seasonId);
        }

        $predictions = $query->orderBy('games.match_date')
            ->select('predictions.points_earned')
            ->get();

        $bestStreak = 0;
        $currentStreak = 0;

        foreach ($predictions as $prediction) {
            if ($prediction->points_earned > 0) {
                $currentStreak++;
                $bestStreak = max($bestStreak, $currentStreak);
            } else {
                $currentStreak = 0;
            }
        }

        return $bestStreak;
    }

    private function getPeriodLeader($period, $seasonId = null)
    {
        $query = User::query()
            ->join('predictions', 'users.id', '=', 'predictions.user_id')
            ->join('games', 'predictions.game_id', '=', 'games.id')
            ->where('games.is_finished', true)
            ->where('users.exclude_from_leaderboard', false);

        if ($seasonId) {
            $query->where('games.season_id', $seasonId);
        }

        if ($period === 'week') {
            $query->whereBetween('games.match_date', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek()
            ]);
        } elseif ($period === 'month') {
            $query->whereMonth('games.match_date', Carbon::now()->month)
                ->whereYear('games.match_date', Carbon::now()->year);
        }

        $leader = $query->select('users.id', 'users.name', 'users.avatar', DB::raw('SUM(predictions.points_earned) as total_points'))
            ->groupBy('users.id', 'users.name', 'users.avatar')
            ->orderByDesc('total_points')
            ->first();

        return $leader;
    }

    public function userStats($userId)
    {
        $user = User::findOrFail($userId);

        abort_if(auth()->id() !== $user->id && ! auth()->user()->is_admin, 403);
        $pointsRule = PointsRule::where('is_active', true)->first();

        // Get all predictions with games
        $predictions = Prediction::query()
            ->join('games', 'predictions.game_id', '=', 'games.id')
            ->where('predictions.user_id', $userId)
            ->where('games.is_finished', true)
            ->select('predictions.*', 'games.match_date', 'games.matchday')
            ->orderByDesc('games.match_date')
            ->with(['game.homeTeam', 'game.awayTeam'])
            ->get();

        // Calculate statistics
        $totalPoints = $predictions->sum('points_earned');

        $activeSeason = Season::where('is_active', true)->first();
        if ($activeSeason) {
            $cp = ChampionPrediction::where('user_id', $userId)->where('season_id', $activeSeason->id)->first();
            if ($cp && $cp->isCorrect($activeSeason)) {
                $totalPoints += ChampionPrediction::BONUS_POINTS;
            }
        }
        $predictionsCount = $predictions->count();

        $exactScores = $predictions->where('points_earned', $pointsRule?->exact_score ?? 5)->count();
        $correctDifferences = $predictions->where('points_earned', $pointsRule?->correct_difference ?? 3)->count();
        $correctWinners = $predictions->where('points_earned', $pointsRule?->correct_winner ?? 1)->count();

        // Points evolution over time (grouped by matchday)
        $pointsEvolution = $predictions->groupBy('matchday')->map(function ($group) {
            return [
                'matchday' => $group->first()->matchday,
                'points' => $group->sum('points_earned'),
            ];
        })->sortBy('matchday')->values();

        // Last 10 predictions
        $recentPredictions = $predictions->take(10);

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
            ],
            'stats' => [
                'total_points' => $totalPoints,
                'predictions_count' => $predictionsCount,
                'exact_scores' => $exactScores,
                'correct_differences' => $correctDifferences,
                'correct_winners' => $correctWinners,
                'success_rate' => $predictionsCount > 0 ? round((($predictionsCount - $predictions->where('points_earned', 0)->count()) / $predictionsCount) * 100, 1) : 0,
                'avg_points' => $predictionsCount > 0 ? round($totalPoints / $predictionsCount, 2) : 0,
                'current_streak' => $this->calculateCurrentStreak($userId),
                'best_streak' => $this->calculateBestStreak($userId),
            ],
            'points_evolution' => $pointsEvolution,
            'recent_predictions' => $recentPredictions->map(function ($prediction) {
                return [
                    'home_team' => $prediction->game->homeTeam->short_name,
                    'away_team' => $prediction->game->awayTeam->short_name,
                    'prediction' => $prediction->home_score . '-' . $prediction->away_score,
                    'result' => $prediction->game->home_score . '-' . $prediction->game->away_score,
                    'points' => $prediction->points_earned,
                    'match_date' => $prediction->game->match_date->format('d/m/Y'),
                ];
            }),
        ]);
    }
}
