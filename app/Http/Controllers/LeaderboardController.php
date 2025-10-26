<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Game;
use App\Models\Prediction;
use App\Models\PointsRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LeaderboardController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', 'all'); // all, month, week, matchday
        $matchday = $request->get('matchday');

        // Get points rules for calculation
        $pointsRule = PointsRule::where('is_active', true)->first();

        // Build base query with detailed statistics
        $query = User::query()
            ->select('users.id', 'users.name', 'users.email')
            ->where('users.exclude_from_leaderboard', false);

        // Apply period filters
        $predictionsQuery = Prediction::query()
            ->join('games', 'predictions.game_id', '=', 'games.id')
            ->where('games.is_finished', true);

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

        // Get detailed statistics for each user
        $leaderboard = $query->get()->map(function ($user) use ($predictionsQuery, $pointsRule) {
            $userPredictions = (clone $predictionsQuery)
                ->where('predictions.user_id', $user->id)
                ->get();

            $totalPoints = $userPredictions->sum('points_earned');
            $predictionsCount = $userPredictions->count();

            $exactScores = $userPredictions->where('points_earned', $pointsRule?->exact_score ?? 5)->count();
            $correctDifferences = $userPredictions->where('points_earned', $pointsRule?->correct_difference ?? 3)->count();
            $correctWinners = $userPredictions->where('points_earned', $pointsRule?->correct_winner ?? 1)->count();
            $incorrectPredictions = $userPredictions->where('points_earned', 0)->count();

            $successfulPredictions = $predictionsCount - $incorrectPredictions;
            $successRate = $predictionsCount > 0 ? round(($successfulPredictions / $predictionsCount) * 100, 1) : 0;
            $avgPoints = $predictionsCount > 0 ? round($totalPoints / $predictionsCount, 2) : 0;

            // Calculate current streak
            $currentStreak = $this->calculateCurrentStreak($user->id);
            $bestStreak = $this->calculateBestStreak($user->id);

            return (object) [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
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
            ];
        })->sortByDesc('total_points')->values();

        // Get available matchdays for filter
        $matchdays = Game::distinct()->orderBy('matchday')->pluck('matchday');

        // Calculate period leaders
        $weekLeader = $this->getPeriodLeader('week');
        $monthLeader = $this->getPeriodLeader('month');

        return view('leaderboard.index', compact(
            'leaderboard',
            'period',
            'matchday',
            'matchdays',
            'weekLeader',
            'monthLeader',
            'pointsRule'
        ));
    }

    private function calculateCurrentStreak($userId)
    {
        $predictions = Prediction::query()
            ->join('games', 'predictions.game_id', '=', 'games.id')
            ->where('predictions.user_id', $userId)
            ->where('games.is_finished', true)
            ->orderByDesc('games.match_date')
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

    private function calculateBestStreak($userId)
    {
        $predictions = Prediction::query()
            ->join('games', 'predictions.game_id', '=', 'games.id')
            ->where('predictions.user_id', $userId)
            ->where('games.is_finished', true)
            ->orderBy('games.match_date')
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

    private function getPeriodLeader($period)
    {
        $query = Prediction::query()
            ->join('games', 'predictions.game_id', '=', 'games.id')
            ->join('users', 'predictions.user_id', '=', 'users.id')
            ->where('games.is_finished', true)
            ->where('users.exclude_from_leaderboard', false);

        if ($period === 'week') {
            $query->whereBetween('games.match_date', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek()
            ]);
        } elseif ($period === 'month') {
            $query->whereMonth('games.match_date', Carbon::now()->month)
                ->whereYear('games.match_date', Carbon::now()->year);
        }

        $leader = $query->select('users.name', DB::raw('SUM(predictions.points_earned) as total_points'))
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_points')
            ->first();

        return $leader;
    }

    public function userStats($userId)
    {
        $user = User::findOrFail($userId);
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
