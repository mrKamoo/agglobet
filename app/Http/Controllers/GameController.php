<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Season;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public function index(Request $request)
    {
        $seasons = Season::orderBy('start_date', 'desc')->get();
        $selectedSeasonId = $request->get('season_id', Season::where('is_active', true)->first()?->id ?? $seasons->first()?->id);
        $activeSeason = Season::find($selectedSeasonId);
        
        $matchday = $request->get('matchday', 1);

        $games = Game::with(['homeTeam', 'awayTeam', 'predictions'])
            ->where('season_id', $activeSeason?->id)
            ->where('matchday', $matchday)
            ->orderBy('match_date', 'asc')
            ->get();

        // Get all matchdays for the season
        $matchdays = Game::where('season_id', $activeSeason?->id)
            ->distinct()
            ->orderBy('matchday')
            ->pluck('matchday')
            ->values();

        return view('games.index', compact('games', 'matchday', 'matchdays', 'activeSeason', 'seasons', 'selectedSeasonId'));
    }

    public function getGames(Request $request)
    {
        $seasons = Season::orderBy('start_date', 'desc')->get();
        $selectedSeasonId = $request->get('season_id', Season::where('is_active', true)->first()?->id ?? $seasons->first()?->id);
        $activeSeason = Season::find($selectedSeasonId);

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
            ->where('match_date', '>=', \Carbon\Carbon::now('Europe/Paris'))
            ->orderBy('match_date', 'asc')
            ->value('matchday');

        $query = Game::with(['homeTeam', 'awayTeam', 'predictions' => function ($q) {
            $q->where('user_id', auth()->id());
        }])
            ->where('season_id', $activeSeason->id);

        // Filter by group
        if ($request->has('group') && $request->group !== null && $request->group !== '') {
            $query->where('group', $request->group);
        }

        // Filter by date or matchday
        if ($request->has('date') && $request->date !== null && $request->date !== '') {
            $query->whereDate('match_date', $request->date);
        } elseif ($request->has('matchday') && $request->matchday !== null && $request->matchday !== '') {
            $query->where('matchday', $request->matchday);
        } elseif (!$request->has('status') && !$request->has('search') && !$request->has('group') && $nextMatchday) {
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

        // Get available match dates for the season
        $matchDates = Game::where('season_id', $activeSeason->id)
            ->orderBy('match_date', 'asc')
            ->pluck('match_date')
            ->map(function ($date) {
                return $date->format('Y-m-d');
            })
            ->unique()
            ->values();

        // Get available groups for the season
        $groups = Game::where('season_id', $activeSeason->id)
            ->whereNotNull('group')
            ->distinct()
            ->orderBy('group')
            ->pluck('group')
            ->values();

        // Transform games for API response
        $gamesData = $games->map(function ($game) {
            $userPrediction = $game->predictions->first();

            return [
                'id' => $game->id,
                'matchday' => $game->matchday,
                'round' => $game->round,
                'group' => $game->group,
                'stadium' => $game->stadium,
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
                'can_predict' => !$game->is_finished && !$game->match_date->isPast() && !$game->hasPlaceholderTeams(),
            ];
        });

        return response()->json([
            'games' => $gamesData,
            'season' => [
                'id' => $activeSeason->id,
                'name' => $activeSeason->name,
                'type' => $activeSeason->type,
            ],
            'matchdays' => $matchdays,
            'match_dates' => $matchDates,
            'groups' => $groups,
            'next_matchday' => $nextMatchday,
        ]);
    }

    public function show(Game $game)
    {
        $game->load(['homeTeam', 'awayTeam', 'predictions.user']);

        return view('games.show', compact('game'));
    }
}
