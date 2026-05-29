<?php

namespace App\Http\Controllers;

use App\Models\ChampionPrediction;
use App\Models\Game;
use App\Models\Prediction;
use App\Models\Season;
use Carbon\Carbon;
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
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Vous ne pouvez plus pronostiquer sur ce match.'], 422);
            }
            return back()->with('error', 'Vous ne pouvez plus pronostiquer sur ce match.');
        }

        // Check if teams are officially known
        if ($game->hasPlaceholderTeams()) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Les pronostics sont indisponibles tant que les équipes ne sont pas connues.'], 422);
            }
            return back()->with('error', 'Les pronostics sont indisponibles tant que les équipes ne sont pas connues.');
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

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Pronostic enregistré avec succès !']);
        }

        return back()->with('success', 'Pronostic enregistré avec succès !');
    }

    public function myPredictions()
    {
        $activeSeason = Season::where('is_active', true)->first();
        
        $predictions = Prediction::whereHas('game', function ($query) use ($activeSeason) {
                if ($activeSeason) {
                    $query->where('season_id', $activeSeason->id);
                }
            })
            ->with(['game.homeTeam', 'game.awayTeam'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('predictions.my-predictions', compact('predictions'));
    }

    public function storeChampion(Request $request, Season $season)
    {
        $request->validate([
            'team_id' => 'required|exists:teams,id',
        ]);

        $deadline = Carbon::create(2026, 6, 20, 23, 59, 59, 'Europe/Paris');
        if (Carbon::now('Europe/Paris')->greaterThan($deadline)) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'La date limite pour pronostiquer le vainqueur (20/06/2026) est dépassée.'], 422);
            }
            return back()->with('error', 'La date limite pour pronostiquer le vainqueur (20/06/2026) est dépassée.');
        }

        $existing = ChampionPrediction::where('user_id', Auth::id())
            ->where('season_id', $season->id)
            ->exists();

        if ($existing) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Votre choix de vainqueur est déjà enregistré et est définitif.'], 422);
            }
            return back()->with('error', 'Votre choix de vainqueur est déjà enregistré et est définitif.');
        }

        ChampionPrediction::create([
            'user_id' => Auth::id(),
            'season_id' => $season->id,
            'team_id' => $request->team_id,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Votre équipe favorite pour le titre a été enregistrée avec succès !']);
        }

        return back()->with('success', 'Votre équipe favorite pour le titre a été enregistrée avec succès !');
    }
}
