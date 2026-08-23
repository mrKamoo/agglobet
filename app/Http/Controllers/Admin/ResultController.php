<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\PointsRule;
use App\Models\Prediction;
use Illuminate\Http\Request;

class ResultController extends Controller
{
    public function index()
    {
        $games = Game::with(['homeTeam', 'awayTeam'])
            ->where('is_finished', false)
            ->where('match_date', '<', now())
            ->orderBy('match_date', 'desc')
            ->get();

        return view('admin.results.index', compact('games'));
    }

    public function update(Request $request, Game $game, \App\Services\FootballDataService $service)
    {
        $request->validate([
            'home_score' => 'required|integer|min:0|max:20',
            'away_score' => 'required|integer|min:0|max:20',
        ]);

        $game->update([
            'home_score' => $request->home_score,
            'away_score' => $request->away_score,
            'is_finished' => true,
        ]);

        // Calculate points for all predictions on this game using the service
        $service->calculatePoints($game);

        return redirect()->back()
            ->with('success', 'Résultat enregistré et points calculés avec succès.');
    }
}
