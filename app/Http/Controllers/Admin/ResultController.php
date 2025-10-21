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

    public function update(Request $request, Game $game)
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

        // Calculate points for all predictions on this game
        $this->calculatePoints($game);

        return redirect()->back()
            ->with('success', 'Résultat enregistré et points calculés avec succès.');
    }

    private function calculatePoints(Game $game)
    {
        $activeRule = PointsRule::where('is_active', true)->first();

        if (!$activeRule) {
            return;
        }

        $predictions = Prediction::where('game_id', $game->id)->get();

        foreach ($predictions as $prediction) {
            $points = 0;

            // Exact score
            if ($prediction->home_score == $game->home_score &&
                $prediction->away_score == $game->away_score) {
                $points = $activeRule->exact_score;
            }
            // Correct goal difference
            elseif (($prediction->home_score - $prediction->away_score) ==
                    ($game->home_score - $game->away_score)) {
                $points = $activeRule->correct_difference;
            }
            // Correct winner or draw
            elseif ($this->getResult($prediction->home_score, $prediction->away_score) ==
                    $this->getResult($game->home_score, $game->away_score)) {
                $points = $activeRule->correct_winner;
            }

            $prediction->update(['points_earned' => $points]);
        }
    }

    private function getResult($homeScore, $awayScore)
    {
        if ($homeScore > $awayScore) {
            return 'home';
        } elseif ($homeScore < $awayScore) {
            return 'away';
        }
        return 'draw';
    }
}
