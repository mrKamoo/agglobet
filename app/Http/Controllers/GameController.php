<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Season;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public function index(Request $request)
    {
        $activeSeason = Season::where('is_active', true)->first();
        $matchday = $request->get('matchday', 1);

        $games = Game::with(['homeTeam', 'awayTeam', 'predictions'])
            ->where('season_id', $activeSeason?->id)
            ->where('matchday', $matchday)
            ->orderBy('match_date', 'asc')
            ->get();

        // Get all matchdays for the season
        $matchdays = range(1, 34); // Ligue 1 has 34 matchdays

        return view('games.index', compact('games', 'matchday', 'matchdays', 'activeSeason'));
    }

    public function getGames(Request $request)
    {
        $activeSeason = Season::where('is_active', true)->first();

        if (!$activeSeason) {
            return response()->json([
                'games' => [],
                'season' => null,
                'matchdays' => [],
                'next_matchday' => null,
            ]);
        }

        // Determine next matchday (upcoming matches)
        $nextMatchday = Game::where('season_id', $activeSeason->id)
            ->where('is_finished', false)
            ->where('match_date', '>=', now())
            ->orderBy('match_date', 'asc')
            ->value('matchday');

        $query = Game::with(['homeTeam', 'awayTeam', 'predictions' => function ($q) {
            $q->where('user_id', auth()->id());
        }])
            ->where('season_id', $activeSeason->id);

        // Filter by matchday
        // If no matchday filter is provided and no other filters are active, default to next matchday
        if ($request->has('matchday') && $request->matchday !== null && $request->matchday !== '') {
            $query->where('matchday', $request->matchday);
        } elseif (!$request->has('status') && !$request->has('search') && $nextMatchday) {
            // Only apply default matchday if no filters are active
            $query->where('matchday', $nextMatchday);
        }

        // Filter by team (search)
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('homeTeam', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('short_name', 'like', "%{$search}%");
                })->orWhereHas('awayTeam', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('short_name', 'like', "%{$search}%");
                });
            });
        }

        // Filter by status
        if ($request->has('status')) {
            if ($request->status === 'finished') {
                $query->where('is_finished', true);
            } elseif ($request->status === 'upcoming') {
                $query->where('is_finished', false);
            }
        }

        $games = $query->orderBy('match_date', 'asc')->get();

        // Get available matchdays for the season
        $matchdays = Game::where('season_id', $activeSeason->id)
            ->distinct()
            ->orderBy('matchday')
            ->pluck('matchday')
            ->values();

        // Transform games for API response
        $gamesData = $games->map(function ($game) {
            $userPrediction = $game->predictions->first();

            return [
                'id' => $game->id,
                'matchday' => $game->matchday,
                'match_date' => $game->match_date->toIso8601String(),
                'match_date_formatted' => $game->match_date->format('d/m/Y H:i'),
                'is_finished' => $game->is_finished,
                'is_past' => $game->match_date->isPast(),
                'home_score' => $game->home_score,
                'away_score' => $game->away_score,
                'home_team' => [
                    'id' => $game->homeTeam->id,
                    'name' => $game->homeTeam->name,
                    'short_name' => $game->homeTeam->short_name,
                    'logo' => $game->homeTeam->logo,
                    'form' => $game->homeTeam->getLastFiveGames($game->id),
                ],
                'away_team' => [
                    'id' => $game->awayTeam->id,
                    'name' => $game->awayTeam->name,
                    'short_name' => $game->awayTeam->short_name,
                    'logo' => $game->awayTeam->logo,
                    'form' => $game->awayTeam->getLastFiveGames($game->id),
                ],
                'user_prediction' => $userPrediction ? [
                    'id' => $userPrediction->id,
                    'home_score' => $userPrediction->home_score,
                    'away_score' => $userPrediction->away_score,
                    'points_earned' => $userPrediction->points_earned,
                ] : null,
                'can_predict' => !$game->is_finished && !$game->match_date->isPast(),
            ];
        });

        return response()->json([
            'games' => $gamesData,
            'season' => [
                'id' => $activeSeason->id,
                'name' => $activeSeason->name,
            ],
            'matchdays' => $matchdays,
            'next_matchday' => $nextMatchday,
        ]);
    }

    public function show(Game $game)
    {
        $game->load(['homeTeam', 'awayTeam', 'predictions.user']);

        return view('games.show', compact('game'));
    }
}
