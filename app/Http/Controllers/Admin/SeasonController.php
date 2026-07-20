<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Season;
use App\Models\Team;
use Illuminate\Http\Request;

class SeasonController extends Controller
{
    public function index()
    {
        $seasons = Season::orderBy('start_date', 'desc')->get();
        return view('admin.seasons.index', compact('seasons'));
    }

    public function create()
    {
        $teams = Team::orderBy('name')->get();
        return view('admin.seasons.create', compact('teams'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'type' => 'required|string|in:league,tournament',
            'competition_id' => 'nullable|integer',
            'winner_team_id' => 'nullable|exists:teams,id',
        ]);

        // Quand une nouvelle compétition est créée, elle prend le dessus (is_active = true)
        // et désactive toutes les autres saisons existantes.
        Season::query()->update(['is_active' => false]);

        Season::create(array_merge($request->all(), ['is_active' => true]));

        return redirect()->route('admin.seasons.index')
            ->with('success', 'Saison créée avec succès et définie comme active.');
    }

    public function edit(Season $season)
    {
        $teams = Team::orderBy('name')->get();
        return view('admin.seasons.edit', compact('season', 'teams'));
    }

    public function update(Request $request, Season $season)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'boolean',
            'type' => 'required|string|in:league,tournament',
            'competition_id' => 'nullable|integer',
            'winner_team_id' => 'nullable|exists:teams,id',
        ]);

        // If activating this season, deactivate all others
        if ($request->is_active) {
            Season::where('id', '!=', $season->id)->update(['is_active' => false]);
        }

        $season->update($request->all());

        return redirect()->route('admin.seasons.index')
            ->with('success', 'Saison mise à jour avec succès.');
    }

    public function destroy(Season $season)
    {
        $season->delete();

        return redirect()->route('admin.seasons.index')
            ->with('success', 'Saison supprimée avec succès.');
    }
}
