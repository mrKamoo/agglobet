<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaderboardController extends Controller
{
    public function index()
    {
        $leaderboard = User::select('users.*')
            ->leftJoin('predictions', 'users.id', '=', 'predictions.user_id')
            ->select('users.id', 'users.name', 'users.email')
            ->selectRaw('COALESCE(SUM(predictions.points_earned), 0) as total_points')
            ->selectRaw('COUNT(predictions.id) as predictions_count')
            ->where('users.exclude_from_leaderboard', false)
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderByDesc('total_points')
            ->get();

        return view('leaderboard.index', compact('leaderboard'));
    }
}
