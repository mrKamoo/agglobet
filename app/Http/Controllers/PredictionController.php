<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Prediction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PredictionController extends Controller
{
    public function store(Request $request, Game $game)
    {
        $request->validate([
            'home_score' => 'required|integer|min:0|max:20',
            'away_score' => 'required|integer|min:0|max:20',
        ]);

        // Check if game has already started
        if ($game->match_date->isPast()) {
            return back()->with('error', 'Vous ne pouvez plus pronostiquer sur ce match.');
        }

        Prediction::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'game_id' => $game->id,
            ],
            [
                'home_score' => $request->home_score,
                'away_score' => $request->away_score,
            ]
        );

        return back()->with('success', 'Pronostic enregistré avec succès !');
    }

    public function myPredictions()
    {
        $predictions = Prediction::with(['game.homeTeam', 'game.awayTeam'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('predictions.my-predictions', compact('predictions'));
    }
}
