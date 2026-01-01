<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Prediction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // 1. User Stats & Rank
        $totalPoints = Prediction::where('user_id', $user->id)->sum('points_earned');
        
        // Calculate Rank
        // We can do this by counting how many users have more points
        $rank = User::where('exclude_from_leaderboard', false)
            ->join('predictions', 'users.id', '=', 'predictions.user_id')
            ->select('users.id', DB::raw('SUM(predictions.points_earned) as total_points'))
            ->groupBy('users.id')
            ->having('total_points', '>', $totalPoints)
            ->get()
            ->count() + 1;

        // 2. Next Match to Predict
        // Find the next game that hasn't started yet
        $nextGame = Game::where('match_date', '>', Carbon::now())
            ->orderBy('match_date', 'asc')
            ->with(['homeTeam', 'awayTeam'])
            ->first();
            
        // Check if user already predicted this game
        $hasPredictedNextGame = false;
        if ($nextGame) {
            $hasPredictedNextGame = Prediction::where('user_id', $user->id)
                ->where('game_id', $nextGame->id)
                ->exists();
        }

        // 3. Recent Activity (Last 5 finished games)
        $recentPredictions = Prediction::where('user_id', $user->id)
            ->join('games', 'predictions.game_id', '=', 'games.id')
            ->where('games.is_finished', true)
            ->orderByDesc('games.match_date')
            ->take(5)
            ->select('predictions.*', 'games.home_score as real_home_score', 'games.away_score as real_away_score', 'games.match_date')
            ->with(['game.homeTeam', 'game.awayTeam'])
            ->get();

        // 4. Global Stats for "Quick View"
        $predictionsCount = Prediction::where('user_id', $user->id)
            ->join('games', 'predictions.game_id', '=', 'games.id')
            ->where('games.is_finished', true)
            ->count();
            
        $correctPredictions = Prediction::where('user_id', $user->id)
            ->where('points_earned', '>', 0)
            ->count();
            
        $successRate = $predictionsCount > 0 ? round(($correctPredictions / $predictionsCount) * 100) : 0;

        return view('dashboard', compact(
            'user',
            'totalPoints',
            'rank',
            'nextGame',
            'hasPredictedNextGame',
            'recentPredictions',
            'successRate'
        ));
    }
}
