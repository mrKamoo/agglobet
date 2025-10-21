<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Season;
use App\Models\Team;
use Illuminate\Http\Request;

class GameManagementController extends Controller
{
    public function index(Request $request)
    {
        $seasons = Season::orderBy('start_date', 'desc')->get();
        $selectedSeason = $request->get('season_id', Season::where('is_active', true)->first()?->id);
        $selectedMatchday = $request->get('matchday');

        // Get available matchdays for the selected season
        $matchdays = Game::when($selectedSeason, function ($query) use ($selectedSeason) {
                $query->where('season_id', $selectedSeason);
            })
            ->distinct()
            ->orderBy('matchday')
            ->pluck('matchday');

        $games = Game::with(['homeTeam', 'awayTeam', 'season'])
            ->when($selectedSeason, function ($query) use ($selectedSeason) {
                $query->where('season_id', $selectedSeason);
            })
            ->when($selectedMatchday, function ($query) use ($selectedMatchday) {
                $query->where('matchday', $selectedMatchday);
            })
            ->orderBy('matchday')
            ->orderBy('match_date')
            ->paginate(20);

        return view('admin.games.index', compact('games', 'seasons', 'selectedSeason', 'matchdays', 'selectedMatchday'));
    }

    public function create()
    {
        $seasons = Season::orderBy('start_date', 'desc')->get();
        $teams = Team::orderBy('name')->get();
        return view('admin.games.create', compact('seasons', 'teams'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'season_id' => 'required|exists:seasons,id',
            'home_team_id' => 'required|exists:teams,id',
            'away_team_id' => 'required|exists:teams,id|different:home_team_id',
            'matchday' => 'required|integer|min:1|max:34',
            'match_date' => 'required|date',
        ]);

        Game::create($request->all());

        return redirect()->route('admin.games.index')
            ->with('success', 'Match créé avec succès.');
    }

    public function edit(Game $game)
    {
        $seasons = Season::orderBy('start_date', 'desc')->get();
        $teams = Team::orderBy('name')->get();
        return view('admin.games.edit', compact('game', 'seasons', 'teams'));
    }

    public function update(Request $request, Game $game)
    {
        $request->validate([
            'season_id' => 'required|exists:seasons,id',
            'home_team_id' => 'required|exists:teams,id',
            'away_team_id' => 'required|exists:teams,id|different:home_team_id',
            'matchday' => 'required|integer|min:1|max:34',
            'match_date' => 'required|date',
        ]);

        $game->update($request->all());

        return redirect()->route('admin.games.index')
            ->with('success', 'Match mis à jour avec succès.');
    }

    public function destroy(Game $game)
    {
        $game->delete();

        return redirect()->route('admin.games.index')
            ->with('success', 'Match supprimé avec succès.');
    }
}
