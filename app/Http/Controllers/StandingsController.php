<?php

namespace App\Http\Controllers;

use App\Services\FootballDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class StandingsController extends Controller
{
    public function __construct(
        private FootballDataService $footballDataService
    ) {}

    public function index()
    {
        try {
            // Mettre en cache le classement pendant 1 heure (3600 secondes)
            // pour éviter de surcharger l'API (limite de 10 requêtes/minute)
            $standingsData = Cache::remember('ligue1_standings', 3600, function () {
                return $this->footballDataService->fetchStandings();
            });

            return view('standings.index', [
                'standings' => $standingsData['standings'],
                'competition' => $standingsData['competition'],
                'season' => $standingsData['season'],
            ]);
        } catch (\Exception $e) {
            return back()->with('error', 'Impossible de récupérer le classement : ' . $e->getMessage());
        }
    }
}
