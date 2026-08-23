<?php

namespace App\Services;

use App\Models\Game;
use App\Models\Season;
use App\Models\Team;
use App\Models\PointsRule;
use App\Models\Prediction;
use App\Models\ApiLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class FootballDataService
{
    private string $apiKey;
    private string $baseUrl = 'https://api.football-data.org/v4';
    private int $defaultCompetitionId = 2015; // Ligue 1 par défaut

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

        // Résoudre le competition_id depuis la saison ou la valeur par défaut
        $competitionId = $season->competition_id ?? $this->defaultCompetitionId;

        try {
            $response = Http::withHeaders([
                'X-Auth-Token' => $this->apiKey,
            ])->get("{$this->baseUrl}/competitions/{$competitionId}/matches", [
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
            'points_calculated' => 0,
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

        if ($stats['skipped'] > 0) {
             ApiLog::create([
                'type' => 'matches',
                'status' => 'warning',
                'message' => "Synchronisation terminée avec des erreurs: {$stats['skipped']} matchs ignorés.",
                'details' => $stats,
            ]);
        } else {
             ApiLog::create([
                'type' => 'matches',
                'status' => 'success',
                'message' => 'Synchronisation des matchs terminée avec succès.',
                'details' => $stats,
            ]);
        }

        return $stats;
    }

    /**
     * Synchronise un match individuel
     */
    private function syncMatch(array $matchData, Season $season, array &$stats): void
    {
        // Récupérer les équipes
        $homeTeam = $this->findOrCreateTeam($matchData['homeTeam'], $matchData['id'] ?? null, 'Dom.');
        $awayTeam = $this->findOrCreateTeam($matchData['awayTeam'], $matchData['id'] ?? null, 'Ext.');

        if (!$homeTeam || !$awayTeam) {
            $stats['skipped']++;
            return;
        }

        // Déterminer le round et le groupe
        $round = $this->translateStage($matchData['stage'] ?? null);
        $group = isset($matchData['group']) ? str_replace('GROUP_', '', $matchData['group']) : null;
        $stadium = $matchData['venue'] ?? null;
        if (empty($stadium)) {
            $mapping = $this->getKnockoutMatchMapping($matchData['id'] ?? null);
            $stadium = $mapping['lieu'] ?? null;
        }

        // Déterminer la journée (matchday)
        // Pour les tournois, si le matchday de l'API est nul, on le calcule à partir de l'étape (round)
        $matchday = $matchData['matchday'] ?? null;
        if (!$matchday && $season->isTournament()) {
            $roundToMatchday = [
                'Phase de groupes'       => 1,
                '16es de finale'         => 4,
                '8es de finale'          => 5,
                'Quarts de finale'       => 6,
                'Demi-finale'            => 7,
                'Match pour la 3e place' => 8,
                'Finale'                 => 9,
            ];
            $matchday = $roundToMatchday[$round] ?? 1;
        }

        // Date du match
        $matchDate = Carbon::parse($matchData['utcDate'])->setTimezone('Europe/Paris');

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
        $game = null;
        if (isset($matchData['id'])) {
            $game = Game::where('season_id', $season->id)
                ->where('api_id', $matchData['id'])
                ->first();
        }

        if (!$game) {
            $gameQuery = Game::where('season_id', $season->id)
                ->where('home_team_id', $homeTeam->id)
                ->where('away_team_id', $awayTeam->id);

            if ($season->isTournament()) {
                $gameQuery->where('round', $round);
                if ($group) {
                    $gameQuery->where('group', $group);
                }
            } else {
                $gameQuery->where('matchday', $matchday);
            }

            $game = $gameQuery->first();
        }

        $shouldCalculatePoints = false;

        if ($game) {
            // Mettre à jour uniquement si le statut, les scores, les équipes ou la date ont changé
            if ($game->is_finished != $isFinished ||
                $game->home_score != $homeScore ||
                $game->away_score != $awayScore ||
                $game->home_team_id != $homeTeam->id ||
                $game->away_team_id != $awayTeam->id ||
                $game->api_id != ($matchData['id'] ?? null) ||
                $game->match_date->ne($matchDate)) {

                $wasNotFinished = !$game->is_finished;
                $scoresChanged = ($game->home_score != $homeScore || $game->away_score != $awayScore);

                $game->update([
                    'api_id' => $matchData['id'] ?? $game->api_id,
                    'home_team_id' => $homeTeam->id,
                    'away_team_id' => $awayTeam->id,
                    'match_date' => $matchDate,
                    'home_score' => $homeScore,
                    'away_score' => $awayScore,
                    'is_finished' => $isFinished,
                    'round' => $round,
                    'group' => $group,
                    'stadium' => $stadium ?? $game->stadium,
                ]);

                $stats['updated']++;

                // Calculer les points si le match vient d'être marqué comme terminé ou si les scores d'un match terminé ont changé
                if (($isFinished && $wasNotFinished) || ($isFinished && $scoresChanged)) {
                    $shouldCalculatePoints = true;
                }
            }
        } else {
            // Créer un nouveau match
            $game = Game::create([
                'season_id' => $season->id,
                'api_id' => $matchData['id'] ?? null,
                'home_team_id' => $homeTeam->id,
                'away_team_id' => $awayTeam->id,
                'matchday' => $matchday,
                'match_date' => $matchDate,
                'home_score' => $homeScore,
                'away_score' => $awayScore,
                'is_finished' => $isFinished,
                'round' => $round,
                'group' => $group,
                'stadium' => $stadium,
            ]);

            $stats['created']++;

            // Calculer les points si le match est déjà terminé
            if ($isFinished) {
                $shouldCalculatePoints = true;
            }
        }

        // Si c'est la finale et que le match est terminé, on détermine le champion de la saison !
        if ($round === 'Finale' && $isFinished) {
            $winnerType = $matchData['score']['winner'] ?? null;
            $winnerTeamId = null;
            if ($winnerType === 'HOME_TEAM') {
                $winnerTeamId = $homeTeam->id;
            } elseif ($winnerType === 'AWAY_TEAM') {
                $winnerTeamId = $awayTeam->id;
            } else {
                // Au cas où le winner n'est pas spécifié mais que les scores départagent
                if ($homeScore > $awayScore) {
                    $winnerTeamId = $homeTeam->id;
                } elseif ($awayScore > $homeScore) {
                    $winnerTeamId = $awayTeam->id;
                }
            }

            if ($winnerTeamId && $season->winner_team_id !== $winnerTeamId) {
                $season->update(['winner_team_id' => $winnerTeamId]);
                Log::info("Vainqueur de la saison {$season->name} mis à jour : équipe ID {$winnerTeamId}");
            }
        }

        // Calculer les points si nécessaire
        if ($shouldCalculatePoints) {
            $this->calculatePoints($game);
            $stats['points_calculated']++;
        }
    }

    /**
     * Calcule les points pour tous les pronostics d'un match
     */
    public function calculatePoints(Game $game): void
    {
        if (!$game->is_finished) {
            return;
        }

        $activeRule = PointsRule::where('is_active', true)->first();

        if (!$activeRule) {
            Log::warning('No active points rule found. Attempting to activate or create a default rule.');
            
            // Check if any rule exists
            $activeRule = PointsRule::first();
            if ($activeRule) {
                $activeRule->update(['is_active' => true]);
            } else {
                $activeRule = PointsRule::create([
                    'name' => 'Règle standard',
                    'description' => 'Système de points standard généré automatiquement',
                    'exact_score' => 5,
                    'correct_difference' => 3,
                    'correct_winner' => 1,
                    'is_active' => true,
                ]);
            }
        }

        $predictions = Prediction::where('game_id', $game->id)->get();

        foreach ($predictions as $prediction) {
            $points = 0;

            // Score exact
            if ($prediction->home_score == $game->home_score &&
                $prediction->away_score == $game->away_score) {
                $points = $activeRule->exact_score;
            }
            // Bonne différence de buts
            elseif (($prediction->home_score - $prediction->away_score) ==
                    ($game->home_score - $game->away_score)) {
                $points = $activeRule->correct_difference;
            }
            // Bon vainqueur ou match nul
            elseif ($this->getResult($prediction->home_score, $prediction->away_score) ==
                    $this->getResult($game->home_score, $game->away_score)) {
                $points = $activeRule->correct_winner;
            }

            $prediction->timestamps = false;
            $prediction->update(['points_earned' => $points]);
        }
    }

    /**
     * Recalcule les points pour tous les matchs terminés
     */
    public function recalculateAllPoints(?Season $season = null): array
    {
        $query = Game::where('is_finished', true);

        if ($season) {
            $query->where('season_id', $season->id);
        }

        $finishedGames = $query->get();

        $stats = [
            'total_games' => $finishedGames->count(),
            'total_predictions' => 0,
        ];

        foreach ($finishedGames as $game) {
            $predictionsCount = Prediction::where('game_id', $game->id)->count();
            $stats['total_predictions'] += $predictionsCount;
            $this->calculatePoints($game);
        }

        ApiLog::create([
            'type' => 'recalc',
            'status' => 'success',
            'message' => 'Recalcul des points terminé.',
            'details' => $stats,
        ]);

        return $stats;
    }

    /**
     * Détermine le résultat d'un match
     */
    private function getResult($homeScore, $awayScore): string
    {
        if ($homeScore > $awayScore) {
            return 'home';
        } elseif ($homeScore < $awayScore) {
            return 'away';
        }
        return 'draw';
    }

    /**
     * Traduit le stage d'anglais en français
     */
    private function translateStage(?string $stage): ?string
    {
        if (!$stage) return null;

        $stages = [
            'GROUP_STAGE' => 'Phase de groupes',
            'LAST_32' => '16es de finale',
            'LAST_16' => '8es de finale',
            'QUARTER_FINALS' => 'Quarts de finale',
            'SEMI_FINALS' => 'Demi-finale',
            'THIRD_PLACE' => 'Match pour la 3e place',
            'FINAL' => 'Finale',
        ];

        return $stages[$stage] ?? $stage;
    }

    /**
     * Trouve ou crée une équipe
     */
    private function findOrCreateTeam(array $teamData, ?int $matchId = null, ?string $type = null): ?Team
    {
        $teamName = $teamData['name'] ?? $teamData['shortName'] ?? null;

        if (empty($teamName)) {
            if ($matchId && $type) {
                $mapping = $this->getKnockoutMatchMapping($matchId);
                $teamName = $mapping[$type] ?? "À déterminer ({$type} #{$matchId})";
                $teamData['shortName'] = 'TBD';
            } else {
                return null;
            }
        }

        // Traduire le nom de l'équipe pour éviter les doublons avec les noms français
        $teamName = $this->translateTeamName($teamName);
        $translatedShortName = isset($teamData['shortName']) ? $this->translateTeamName($teamData['shortName']) : null;

        // Chercher l'équipe par nom (ou par short_name si ce n'est pas une équipe temporaire)
        $query = Team::where('name', $teamName);
        if (!str_contains($teamName, 'À déterminer') && !str_contains($teamName, 'Groupe') && !str_contains($teamName, 'Vainqueur') && !str_contains($teamName, 'Perdant')) {
            $query->orWhere('short_name', $teamData['shortName'] ?? $teamName)
                  ->orWhere('short_name', $translatedShortName ?? $teamName)
                  ->orWhere('name', $teamData['shortName'] ?? $teamName);
        }
        $team = $query->first();

        if ($team) {
            // Mettre à jour le logo et le short_name si disponible
            $updateData = [];
            if (isset($teamData['crest']) && empty($team->logo)) {
                $updateData['logo'] = $teamData['crest'];
            }
            if ($translatedShortName && $team->short_name !== $translatedShortName) {
                $updateData['short_name'] = $translatedShortName;
            }
            if (!empty($updateData)) {
                $team->update($updateData);
            }
            return $team;
        }

        // Créer une nouvelle équipe si elle n'existe pas
        return Team::create([
            'name' => $teamName,
            'short_name' => $translatedShortName ?? $teamData['shortName'] ?? $teamData['tla'] ?? null,
            'logo' => $teamData['crest'] ?? null,
        ]);
    }

    /**
     * Traduit le nom d'une équipe de l'anglais vers le français
     */
    private function translateTeamName(string $name): string
    {
        $translations = [
            'Mexico' => 'Mexique',
            'South Africa' => 'Afrique du Sud',
            'South Korea' => 'Corée du Sud',
            'Korea Republic' => 'Corée du Sud',
            'Czechia' => 'République Tchèque',
            'Germany' => 'Allemagne',
            'Argentina' => 'Argentine',
            'Brazil' => 'Brésil',
            'Morocco' => 'Maroc',
            'United States' => 'États-Unis',
            'USA' => 'États-Unis',
            'Bosnia-Herzegovina' => 'Bosnie-Herzégovine',
            'Saudi Arabia' => 'Arabie Saoudite',
            'Tunisia' => 'Tunisie',
            'Turkey' => 'Turquie',
            'Belgium' => 'Belgique',
            'Austria' => 'Autriche',
            'Colombia' => 'Colombie',
            'Egypt' => 'Égypte',
            'Haiti' => 'Haïti',
            'Congo DR' => 'RD Congo',
            'DR Congo' => 'RD Congo',
            'Ivory Coast' => "Côte d'Ivoire",
            'Jordan' => 'Jordanie',
            'Iraq' => 'Irak',
            'Uzbekistan' => 'Ouzbékistan',
            'Netherlands' => 'Pays-Bas',
            'Norway' => 'Norvège',
            'Scotland' => 'Écosse',
            'England' => 'Angleterre',
            'Croatia' => 'Croatie',
            'Spain' => 'Espagne',
            'Portugal' => 'Portugal',
            'Uruguay' => 'Uruguay',
            'Senegal' => 'Sénégal',
            'Algeria' => 'Algérie',
            'Sweden' => 'Suède',
            'Switzerland' => 'Suisse',
            'Ecuador' => 'Équateur',
            'Japan' => 'Japon',
            'Italy' => 'Italie',
            'Poland' => 'Pologne',
            'Denmark' => 'Danemark',
            'Ukraine' => 'Ukraine',
            'Wales' => 'Pays de Galles',
            'Cameroon' => 'Cameroun',
            'Ghana' => 'Ghana',
            'Canada' => 'Canada',
            'Paraguay' => 'Paraguay',
            'Chile' => 'Chili',
            'Peru' => 'Pérou',
            'Australia' => 'Australie',
            'New Zealand' => 'Nouvelle-Zélande',
            'Cape Verde Islands' => 'Cap-Vert',
            'Cape Verde' => 'Cap-Vert',
            'Iran' => 'Iran',
            'Panama' => 'Panama',
            'Qatar' => 'Qatar',
            'Curaçao' => 'Curaçao',
            'China' => 'Chine',
            'Honduras' => 'Honduras',
            'Costa Rica' => 'Costa Rica',
            'Jamaica' => 'Jamaïque',
            'El Salvador' => 'Salvador',
            'Trinidad and Tobago' => 'Trinité-et-Tobago',
            'Guatemala' => 'Guatemala',
            'Bolivia' => 'Bolivie',
            'Venezuela' => 'Venezuela',
            'Nigeria' => 'Nigéria',
            'Mali' => 'Mali',
            'Burkina Faso' => 'Burkina Faso',
            'Guinea' => 'Guinée',
            'Gabon' => 'Gabon',
            'Zambia' => 'Zambie',
            'Uganda' => 'Ouganda',
            'Benin' => 'Bénin',
            'Equatorial Guinea' => 'Guinée Équatoriale',
            'Togo' => 'Togo',
            'Angola' => 'Angola',
            'United Arab Emirates' => 'Émirats Arabes Unis',
            'Oman' => 'Oman',
            'Syria' => 'Syrie',
            'Vietnam' => 'Viêt Nam',
            'Lebanon' => 'Liban',
            'India' => 'Inde',
            'Kyrgyzstan' => 'Kirghizistan',
            'Palestine' => 'Palestine',
            'Tajikistan' => 'Tadjikistan',
            'Thailand' => 'Thaïlande',
            'North Korea' => 'Corée du Nord',
            'Greece' => 'Grèce',
            'Finland' => 'Finlande',
            'Iceland' => 'Islande',
            'Slovakia' => 'Slovaquie',
            'Slovenia' => 'Slovénie',
            'Hungary' => 'Hongrie',
            'Romania' => 'Roumanie',
            'Bulgaria' => 'Bulgarie',
            'Serbia' => 'Serbie',
            'Montenegro' => 'Monténégro',
            'North Macedonia' => 'Macédoine du Nord',
            'Albania' => 'Albanie',
            'Kosovo' => 'Kosovo',
            'Georgia' => 'Géorgie',
            'Estonia' => 'Estonie',
            'Latvia' => 'Lettonie',
            'Lithuania' => 'Lituanie',
            'Belarus' => 'Biélorussie',
            'Israel' => 'Israël',
            'Cyprus' => 'Chypre',
            'Malta' => 'Malte',
            'Andorra' => 'Andorre',
            'San Marino' => 'Saint-Marin',
            'Liechtenstein' => 'Liechtenstein',
            'Gibraltar' => 'Gibraltar',
            'Luxembourg' => 'Luxembourg',
            'Republic of Ireland' => 'Irlande',
            'Ireland' => 'Irlande',
            'Northern Ireland' => 'Irlande du Nord',
        ];

        return $translations[$name] ?? $name;
    }

    /**
     * Récupère les métadonnées de mapping Coupe du Monde 2026 pour les matchs éliminatoires
     */
    private function getKnockoutMatchMapping(?int $matchId): ?array
    {
        if (!$matchId) return null;

        $mapping = [
            537417 => ['Dom.' => '2e Groupe A', 'Ext.' => '2e Groupe B', 'lieu' => 'SoFi Stadium, Los Angeles (États-Unis)'],
            537423 => ['Dom.' => '1er Groupe A', 'Ext.' => '3e Groupe C, D ou E', 'lieu' => 'Stade Azteca, Mexico (Mexique)'],
            537415 => ['Dom.' => '1er Groupe B', 'Ext.' => '3e Groupe G, H ou I', 'lieu' => 'BMO Field, Toronto (Canada)'],
            537418 => ['Dom.' => '1er Groupe E', 'Ext.' => '3e Groupe A, B ou C', 'lieu' => 'Gillette Stadium, Boston (États-Unis)'],
            537424 => ['Dom.' => '1er Groupe D', 'Ext.' => '3e Groupe B, E ou F', 'lieu' => 'Levi\'s Stadium, San Francisco (États-Unis)'],
            537416 => ['Dom.' => '1er Groupe C', 'Ext.' => '2e Groupe F', 'lieu' => 'Lumen Field, Seattle (États-Unis)'],
            537425 => ['Dom.' => '1er Groupe F', 'Ext.' => '2e Groupe C', 'lieu' => 'Stade BBVA, Monterrey (Mexique)'],
            537426 => ['Dom.' => '1er Groupe L', 'Ext.' => '3e Groupe E, H ou I', 'lieu' => 'NRG Stadium, Houston (États-Unis)'],
            537422 => ['Dom.' => '1er Groupe G', 'Ext.' => '3e Groupe A, E ou H', 'lieu' => 'Arrowhead Stadium, Kansas City (États-Unis)'],
            537421 => ['Dom.' => '2e Groupe E', 'Ext.' => '2e Groupe I', 'lieu' => 'AT&T Stadium, Dallas (États-Unis)'],
            537420 => ['Dom.' => '1er Groupe I', 'Ext.' => '3e Groupe C, E ou F', 'lieu' => 'Mercedes-Benz Stadium, Atlanta (États-Unis)'],
            537419 => ['Dom.' => '2e Groupe D', 'Ext.' => '2e Groupe G', 'lieu' => 'MetLife Stadium, New York / New Jersey (États-Unis)'],
            537429 => ['Dom.' => '2e Groupe K', 'Ext.' => '2e Groupe L', 'lieu' => 'Hard Rock Stadium, Miami (États-Unis)'],
            537428 => ['Dom.' => '1er Groupe K', 'Ext.' => '3e Groupe I, J ou L', 'lieu' => 'Lincoln Financial Field, Philadelphie (États-Unis)'],
            537427 => ['Dom.' => '1er Groupe J', 'Ext.' => '2e Groupe H', 'lieu' => 'BC Place, Vancouver (Canada)'],
            537430 => ['Dom.' => '1er Groupe H', 'Ext.' => '2e Groupe J', 'lieu' => 'Estadio Akron, Guadalajara (Mexique)'],
            537376 => ['Dom.' => 'Vainqueur Match 73', 'Ext.' => 'Vainqueur Match 74', 'lieu' => 'Hard Rock Stadium, Miami (États-Unis)'],
            537375 => ['Dom.' => 'Vainqueur Match 75', 'Ext.' => 'Vainqueur Match 76', 'lieu' => 'Stade Azteca, Mexico (Mexique)'],
            537377 => ['Dom.' => 'Vainqueur Match 77', 'Ext.' => 'Vainqueur Match 78', 'lieu' => 'MetLife Stadium, New York / New Jersey (États-Unis)'],
            537378 => ['Dom.' => 'Vainqueur Match 79', 'Ext.' => 'Vainqueur Match 80', 'lieu' => 'AT&T Stadium, Dallas (États-Unis)'],
            537379 => ['Dom.' => 'Vainqueur Match 81', 'Ext.' => 'Vainqueur Match 82', 'lieu' => 'Mercedes-Benz Stadium, Atlanta (États-Unis)'],
            537380 => ['Dom.' => 'Vainqueur Match 83', 'Ext.' => 'Vainqueur Match 84', 'lieu' => 'BC Place, Vancouver (Canada)'],
            537381 => ['Dom.' => 'Vainqueur Match 85', 'Ext.' => 'Vainqueur Match 86', 'lieu' => 'Lincoln Financial Field, Philadelphie (États-Unis)'],
            537382 => ['Dom.' => 'Vainqueur Match 87', 'Ext.' => 'Vainqueur Match 88', 'lieu' => 'NRG Stadium, Houston (États-Unis)'],
            537383 => ['Dom.' => 'Vainqueur Match 89', 'Ext.' => 'Vainqueur Match 90', 'lieu' => 'Gillette Stadium, Boston (États-Unis)'],
            537384 => ['Dom.' => 'Vainqueur Match 91', 'Ext.' => 'Vainqueur Match 92', 'lieu' => 'SoFi Stadium, Los Angeles (États-Unis)'],
            537385 => ['Dom.' => 'Vainqueur Match 93', 'Ext.' => 'Vainqueur Match 94', 'lieu' => 'Hard Rock Stadium, Miami (États-Unis)'],
            537386 => ['Dom.' => 'Vainqueur Match 95', 'Ext.' => 'Vainqueur Match 96', 'lieu' => 'Arrowhead Stadium, Kansas City (États-Unis)'],
            537387 => ['Dom.' => 'Vainqueur Match 97', 'Ext.' => 'Vainqueur Match 98', 'lieu' => 'AT&T Stadium, Dallas (États-Unis)'],
            537388 => ['Dom.' => 'Vainqueur Match 99', 'Ext.' => 'Vainqueur Match 100', 'lieu' => 'Mercedes-Benz Stadium, Atlanta (États-Unis)'],
            537389 => ['Dom.' => 'Perdant Match 101', 'Ext.' => 'Perdant Match 102', 'lieu' => 'Hard Rock Stadium, Miami (États-Unis)'],
            537390 => ['Dom.' => 'Vainqueur Match 101', 'Ext.' => 'Vainqueur Match 102', 'lieu' => 'MetLife Stadium, New York / New Jersey (États-Unis)'],
        ];

        return $mapping[$matchId] ?? null;
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
    public function fetchTeams(?Season $season = null): array
    {
        $competitionId = $season?->competition_id ?? $this->defaultCompetitionId;

        try {
            $response = Http::withHeaders([
                'X-Auth-Token' => $this->apiKey,
            ])->get("{$this->baseUrl}/competitions/{$competitionId}/teams");

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
    public function syncTeams(?Season $season = null): array
    {
        $teams = $this->fetchTeams($season);

        $stats = [
            'total' => count($teams),
            'created' => 0,
            'updated' => 0,
        ];

        foreach ($teams as $teamData) {
            $translatedName = $this->translateTeamName($teamData['name'] ?? $teamData['shortName']);
            $translatedShortName = $this->translateTeamName($teamData['shortName'] ?? $teamData['name']);

            $team = Team::where('name', $translatedName)
                ->orWhere('short_name', $teamData['shortName'])
                ->orWhere('short_name', $translatedShortName)
                ->first();

            if ($team) {
                // Mettre à jour les informations
                $team->update([
                    'name' => $translatedName,
                    'short_name' => $translatedShortName ?? $team->short_name,
                    'logo' => $teamData['crest'] ?? $team->logo,
                    'city' => $teamData['area']['name'] ?? $team->city,
                    'stadium' => $teamData['venue'] ?? $team->stadium,
                ]);
                $stats['updated']++;
            } else {
                // Créer une nouvelle équipe
                Team::create([
                    'name' => $translatedName,
                    'short_name' => $translatedShortName ?? $teamData['tla'] ?? null,
                    'logo' => $teamData['crest'] ?? null,
                    'city' => $teamData['area']['name'] ?? null,
                    'stadium' => $teamData['venue'] ?? null,
                ]);
                $stats['created']++;
            }
        }

        ApiLog::create([
            'type' => 'teams',
            'status' => 'success',
            'message' => 'Synchronisation des équipes terminée.',
            'details' => $stats,
        ]);

        return $stats;
    }

    /**
     * Récupère le classement de la Ligue 1
     */
    public function fetchStandings(?Season $season = null): array
    {
        try {
            $params = [];

            if ($season) {
                $params['season'] = $this->extractSeasonYear($season->name);
            }

        $competitionId = $season?->competition_id ?? $this->defaultCompetitionId;

            $response = Http::withHeaders([
                'X-Auth-Token' => $this->apiKey,
            ])->get("{$this->baseUrl}/competitions/{$competitionId}/standings", $params);

            if ($response->failed()) {
                Log::error('Football-Data API Error (Standings)', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new \Exception('Erreur lors de la récupération du classement: ' . $response->status());
            }

            $data = $response->json();

            // Pour un tournoi (Coupe du Monde), il y a plusieurs classements 'TOTAL' (un par groupe)
            if ($season && $season->isTournament()) {
                $standingsData = collect($data['standings'] ?? [])
                    ->filter(fn($s) => $s['type'] === 'TOTAL')
                    ->mapWithKeys(function($s) {
                        // Extraire la lettre du groupe ("Group A" -> "A", "GROUP_A" -> "A")
                        $groupName = str_replace(['GROUP_', 'Group '], '', $s['group'] ?? '');
                        return [$groupName => $s['table'] ?? []];
                    })->toArray();
                    
                return [
                    'competition' => $data['competition'] ?? [],
                    'season' => $data['season'] ?? [],
                    'standings' => $standingsData,
                ];
            }

            // Retourner le classement général (type: 'TOTAL') pour un championnat classique
            $standings = collect($data['standings'] ?? [])
                ->firstWhere('type', 'TOTAL');

            return [
                'competition' => $data['competition'] ?? [],
                'season' => $data['season'] ?? [],
                'standings' => $standings['table'] ?? [],
            ];
        } catch (\Exception $e) {
            Log::error('Football-Data Service Error (Standings)', [
                'message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
