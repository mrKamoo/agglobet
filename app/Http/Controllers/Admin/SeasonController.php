<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Season;
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
        return view('admin.seasons.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        Season::create($request->all());

        return redirect()->route('admin.seasons.index')
            ->with('success', 'Saison créée avec succès.');
    }

    public function edit(Season $season)
    {
        return view('admin.seasons.edit', compact('season'));
    }

    public function update(Request $request, Season $season)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'boolean',
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
