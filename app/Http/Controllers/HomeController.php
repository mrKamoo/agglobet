<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Season;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $activeSeason = Season::where('is_active', true)->first();

        $upcomingGames = Game::with(['homeTeam', 'awayTeam'])
            ->where('season_id', $activeSeason?->id)
            ->where('is_finished', false)
            ->orderBy('match_date', 'asc')
            ->take(5)
            ->get();

        return view('home', compact('upcomingGames', 'activeSeason'));
    }
}
