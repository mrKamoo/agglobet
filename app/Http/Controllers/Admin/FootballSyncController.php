<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Season;
use App\Services\FootballDataService;
use Illuminate\Http\Request;

class FootballSyncController extends Controller
{
    /**
     * Affiche la page de synchronisation
     */
    public function index()
    {
        $seasons = Season::orderBy('start_date', 'desc')->get();
        $activeSeason = Season::where('is_active', true)->first();

        return view('admin.sync.index', compact('seasons', 'activeSeason'));
    }

    /**
     * Synchronise les matchs depuis l'API Football-Data.org
     */
    public function syncMatches(Request $request, FootballDataService $service)
    {
        try {
            $seasonId = $request->get('season_id');
            $season = $seasonId
                ? Season::findOrFail($seasonId)
                : Season::where('is_active', true)->first();

            if (!$season) {
                return back()->with('error', 'Aucune saison active trouvée.');
            }

            $stats = $service->syncMatches($season);

            $message = sprintf(
                'Synchronisation terminée: %d matchs récupérés, %d créés, %d mis à jour, %d ignorés.',
                $stats['total'],
                $stats['created'],
                $stats['updated'],
                $stats['skipped']
            );

            // Ajouter info sur les points calculés si présent
            if (isset($stats['points_calculated']) && $stats['points_calculated'] > 0) {
                $message .= sprintf(' Points calculés pour %d matchs.', $stats['points_calculated']);
            }

            return back()->with('success', $message);

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la synchronisation: ' . $e->getMessage());
        }
    }

    /**
     * Synchronise les équipes depuis l'API Football-Data.org
     */
    public function syncTeams(FootballDataService $service)
    {
        try {
            $stats = $service->syncTeams();

            $message = sprintf(
                'Synchronisation des équipes terminée: %d équipes récupérées, %d créées, %d mises à jour.',
                $stats['total'],
                $stats['created'],
                $stats['updated']
            );

            return back()->with('success', $message);

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la synchronisation des équipes: ' . $e->getMessage());
        }
    }

    /**
     * Recalcule les points pour tous les matchs terminés
     */
    public function recalculatePoints(Request $request, FootballDataService $service)
    {
        try {
            $seasonId = $request->get('season_id');
            $season = $seasonId ? Season::findOrFail($seasonId) : null;

            $stats = $service->recalculateAllPoints($season);

            $message = sprintf(
                'Recalcul terminé: %d matchs terminés, %d pronostics mis à jour.',
                $stats['total_games'],
                $stats['total_predictions']
            );

            if ($season) {
                $message .= sprintf(' (Saison: %s)', $season->name);
            } else {
                $message .= ' (Toutes les saisons)';
            }

            return back()->with('success', $message);

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors du recalcul des points: ' . $e->getMessage());
        }
    }
}
