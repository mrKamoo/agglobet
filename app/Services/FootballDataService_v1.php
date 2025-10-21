<?php

namespace App\Services;

use App\Models\Game;
use App\Models\Season;
use App\Models\Team;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class FootballDataService
{
    private string $apiKey;
    private string $baseUrl = 'https://api.football-data.org/v4';
    private int $ligue1Id = 2015; // ID de la Ligue 1 sur Football-Data.org

    public function __construct()
    {
        $this->apiKey = config('services.football_data.api_key');
    }

    /**
     * Récupère tous les matchs de la Ligue 1 pour une saison
     */
    public function fetchMatches(?Season $season = null): array
    {
        if (!$season) {
            $season = Season::where('is_active', true)->first();
        }

        if (!$season) {
            throw new \Exception('Aucune saison active trouvée');
        }

        try {
            $response = Http::withHeaders([
                'X-Auth-Token' => $this->apiKey,
            ])->get("{$this->baseUrl}/competitions/{$this->ligue1Id}/matches", [
                'season' => $this->extractSeasonYear($season->name),
            ]);

            if ($response->failed()) {
                Log::error('Football-Data API Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new \Exception('Erreur lors de la récupération des matchs: ' . $response->status());
            }

            return $response->json()['matches'] ?? [];
        } catch (\Exception $e) {
            Log::error('Football-Data Service Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Synchronise les matchs de l'API avec la base de données
     */
    public function syncMatches(?Season $season = null): array
    {
        $matches = $this->fetchMatches($season);

        if (!$season) {
            $season = Season::where('is_active', true)->first();
        }

        $stats = [
            'total' => count($matches),
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
        ];

        foreach ($matches as $match) {
            try {
                $this->syncMatch($match, $season, $stats);
            } catch (\Exception $e) {
                Log::warning('Error syncing match', [
                    'match_id' => $match['id'] ?? 'unknown',
                    'error' => $e->getMessage(),
                ]);
                $stats['skipped']++;
            }
        }

        return $stats;
    }

    /**
     * Synchronise un match individuel
     */
    private function syncMatch(array $matchData, Season $season, array &$stats): void
    {
        // Récupérer les équipes
        $homeTeam = $this->findOrCreateTeam($matchData['homeTeam']);
        $awayTeam = $this->findOrCreateTeam($matchData['awayTeam']);

        if (!$homeTeam || !$awayTeam) {
            $stats['skipped']++;
            return;
        }

        // Déterminer la journée (matchday)
        $matchday = $matchData['matchday'] ?? null;

        // Date du match
        $matchDate = Carbon::parse($matchData['utcDate']);

        // Scores (si le match est terminé)
        $homeScore = null;
        $awayScore = null;
        $isFinished = false;

        if ($matchData['status'] === 'FINISHED') {
            $homeScore = $matchData['score']['fullTime']['home'];
            $awayScore = $matchData['score']['fullTime']['away'];
            $isFinished = true;
        }

        // Vérifier si le match existe déjà
        $game = Game::where('season_id', $season->id)
            ->where('home_team_id', $homeTeam->id)
            ->where('away_team_id', $awayTeam->id)
            ->where('matchday', $matchday)
            ->first();

        if ($game) {
            // Mettre à jour uniquement si le statut a changé ou si les scores ont changé
            if ($game->is_finished != $isFinished ||
                $game->home_score != $homeScore ||
                $game->away_score != $awayScore) {

                $game->update([
                    'match_date' => $matchDate,
                    'home_score' => $homeScore,
                    'away_score' => $awayScore,
                    'is_finished' => $isFinished,
                ]);

                $stats['updated']++;
            }
        } else {
            // Créer un nouveau match
            Game::create([
                'season_id' => $season->id,
                'home_team_id' => $homeTeam->id,
                'away_team_id' => $awayTeam->id,
                'matchday' => $matchday,
                'match_date' => $matchDate,
                'home_score' => $homeScore,
                'away_score' => $awayScore,
                'is_finished' => $isFinished,
            ]);

            $stats['created']++;
        }
    }

    /**
     * Trouve ou crée une équipe
     */
    private function findOrCreateTeam(array $teamData): ?Team
    {
        $teamName = $teamData['name'] ?? $teamData['shortName'] ?? null;

        if (!$teamName) {
            return null;
        }

        // Chercher l'équipe par nom
        $team = Team::where('name', $teamName)
            ->orWhere('short_name', $teamData['shortName'] ?? $teamName)
            ->orWhere('name', $teamData['shortName'] ?? $teamName)
            ->first();

        if ($team) {
            // Mettre à jour le logo si disponible
            if (isset($teamData['crest']) && empty($team->logo)) {
                $team->update(['logo' => $teamData['crest']]);
            }
            return $team;
        }

        // Créer une nouvelle équipe si elle n'existe pas
        return Team::create([
            'name' => $teamName,
            'short_name' => $teamData['shortName'] ?? $teamData['tla'] ?? null,
            'logo' => $teamData['crest'] ?? null,
        ]);
    }

    /**
     * Extrait l'année de la saison (ex: "2024/2025" -> 2024)
     */
    private function extractSeasonYear(string $seasonName): int
    {
        // Cherche le premier nombre à 4 chiffres
        preg_match('/(\d{4})/', $seasonName, $matches);
        return isset($matches[1]) ? (int) $matches[1] : (int) date('Y');
    }

    /**
     * Récupère les équipes de la Ligue 1
     */
    public function fetchTeams(): array
    {
        try {
            $response = Http::withHeaders([
                'X-Auth-Token' => $this->apiKey,
            ])->get("{$this->baseUrl}/competitions/{$this->ligue1Id}/teams");

            if ($response->failed()) {
                Log::error('Football-Data API Error (Teams)', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new \Exception('Erreur lors de la récupération des équipes: ' . $response->status());
            }

            return $response->json()['teams'] ?? [];
        } catch (\Exception $e) {
            Log::error('Football-Data Service Error (Teams)', [
                'message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Synchronise les équipes
     */
    public function syncTeams(): array
    {
        $teams = $this->fetchTeams();

        $stats = [
            'total' => count($teams),
            'created' => 0,
            'updated' => 0,
        ];

        foreach ($teams as $teamData) {
            $team = Team::where('name', $teamData['name'])
                ->orWhere('short_name', $teamData['shortName'])
                ->first();

            if ($team) {
                // Mettre à jour les informations
                $team->update([
                    'short_name' => $teamData['shortName'] ?? $team->short_name,
                    'logo' => $teamData['crest'] ?? $team->logo,
                    'city' => $teamData['area']['name'] ?? $team->city,
                    'stadium' => $teamData['venue'] ?? $team->stadium,
                ]);
                $stats['updated']++;
            } else {
                // Créer une nouvelle équipe
                Team::create([
                    'name' => $teamData['name'],
                    'short_name' => $teamData['shortName'] ?? $teamData['tla'] ?? null,
                    'logo' => $teamData['crest'] ?? null,
                    'city' => $teamData['area']['name'] ?? null,
                    'stadium' => $teamData['venue'] ?? null,
                ]);
                $stats['created']++;
            }
        }

        return $stats;
    }
}
