<?php

namespace App\Http\Controllers;

use App\Models\ChampionPrediction;
use App\Models\Season;
use App\Models\Game;
use App\Models\Team;
use App\Services\FootballDataService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class StandingsController extends Controller
{
    public function __construct(
        private FootballDataService $footballDataService
    ) {}

    public function worldCup()
    {
        $season = Season::where('type', 'tournament')->first();
        if (!$season) {
            return redirect()->route('home')->with('error', 'La Coupe du Monde n\'est pas encore configurée.');
        }

        // 1. Calculer les classements des groupes
        $games = Game::where('season_id', $season->id)
            ->where('round', 'Phase de groupes')
            ->with(['homeTeam', 'awayTeam'])
            ->get();

        $groupStandings = [];

        foreach ($games as $game) {
            $group = $game->group;
            if (!$group) continue;

            if (!isset($groupStandings[$group])) {
                $groupStandings[$group] = [];
            }

            $homeTeam = $game->homeTeam;
            $awayTeam = $game->awayTeam;

            if ($homeTeam) {
                if (!isset($groupStandings[$group][$homeTeam->id])) {
                    $groupStandings[$group][$homeTeam->id] = [
                        'team' => $homeTeam,
                        'played' => 0,
                        'won' => 0,
                        'drawn' => 0,
                        'lost' => 0,
                        'goals_for' => 0,
                        'goals_against' => 0,
                        'goal_diff' => 0,
                        'points' => 0,
                    ];
                }
            }

            if ($awayTeam) {
                if (!isset($groupStandings[$group][$awayTeam->id])) {
                    $groupStandings[$group][$awayTeam->id] = [
                        'team' => $awayTeam,
                        'played' => 0,
                        'won' => 0,
                        'drawn' => 0,
                        'lost' => 0,
                        'goals_for' => 0,
                        'goals_against' => 0,
                        'goal_diff' => 0,
                        'points' => 0,
                    ];
                }
            }

            if ($game->is_finished && $homeTeam && $awayTeam) {
                $hs = $game->home_score;
                $as = $game->away_score;

                // Home
                $groupStandings[$group][$homeTeam->id]['played']++;
                $groupStandings[$group][$homeTeam->id]['goals_for'] += $hs;
                $groupStandings[$group][$homeTeam->id]['goals_against'] += $as;

                // Away
                $groupStandings[$group][$awayTeam->id]['played']++;
                $groupStandings[$group][$awayTeam->id]['goals_for'] += $as;
                $groupStandings[$group][$awayTeam->id]['goals_against'] += $hs;

                if ($hs > $as) {
                    $groupStandings[$group][$homeTeam->id]['won']++;
                    $groupStandings[$group][$homeTeam->id]['points'] += 3;
                    $groupStandings[$group][$awayTeam->id]['lost']++;
                } elseif ($hs < $as) {
                    $groupStandings[$group][$awayTeam->id]['won']++;
                    $groupStandings[$group][$awayTeam->id]['points'] += 3;
                    $groupStandings[$group][$homeTeam->id]['lost']++;
                } else {
                    $groupStandings[$group][$homeTeam->id]['drawn']++;
                    $groupStandings[$group][$homeTeam->id]['points'] += 1;
                    $groupStandings[$group][$awayTeam->id]['drawn']++;
                    $groupStandings[$group][$awayTeam->id]['points'] += 1;
                }
            }
        }

        // Trier les équipes dans chaque groupe
        foreach ($groupStandings as $group => &$teams) {
            foreach ($teams as &$stats) {
                $stats['goal_diff'] = $stats['goals_for'] - $stats['goals_against'];
            }
            uasort($teams, function ($a, $b) {
                // Points
                if ($a['points'] !== $b['points']) {
                    return $b['points'] <=> $a['points'];
                }
                // Différence de buts
                if ($a['goal_diff'] !== $b['goal_diff']) {
                    return $b['goal_diff'] <=> $a['goal_diff'];
                }
                // Buts marqués
                if ($a['goals_for'] !== $b['goals_for']) {
                    return $b['goals_for'] <=> $a['goals_for'];
                }
                // Nom de l'équipe
                return $a['team']->name <=> $b['team']->name;
            });
        }
        unset($teams); // Casser la référence pour éviter les effets de bord
        ksort($groupStandings); // Trier les groupes par A, B, C...

        // 2. Récupérer les vainqueurs, 2es et 3es de chaque groupe
        $winners = [];
        $runnersUp = [];
        $thirdPlacedTeams = [];

        foreach ($groupStandings as $group => $teams) {
            $teamsList = array_values($teams);
            if (isset($teamsList[0])) {
                $winners[$group] = $teamsList[0]['team'];
            }
            if (isset($teamsList[1])) {
                $runnersUp[$group] = $teamsList[1]['team'];
            }
            if (isset($teamsList[2])) {
                $thirdPlacedTeams[] = [
                    'group' => $group,
                    'team' => $teamsList[2]['team'],
                    'played' => $teamsList[2]['played'],
                    'won' => $teamsList[2]['won'],
                    'drawn' => $teamsList[2]['drawn'],
                    'lost' => $teamsList[2]['lost'],
                    'goals_for' => $teamsList[2]['goals_for'],
                    'goals_against' => $teamsList[2]['goals_against'],
                    'goal_diff' => $teamsList[2]['goal_diff'],
                    'points' => $teamsList[2]['points'],
                ];
            }
        }

        // Trier les 3es pour trouver les 8 meilleurs
        usort($thirdPlacedTeams, function ($a, $b) {
            if ($a['points'] !== $b['points']) {
                return $b['points'] <=> $a['points'];
            }
            if ($a['goal_diff'] !== $b['goal_diff']) {
                return $b['goal_diff'] <=> $a['goal_diff'];
            }
            if ($a['goals_for'] !== $b['goals_for']) {
                return $b['goals_for'] <=> $a['goals_for'];
            }
            return $a['team']->name <=> $b['team']->name;
        });

        $best3rds = array_slice($thirdPlacedTeams, 0, 8);

        // 3. Récupérer tous les matchs de phase éliminatoire pour le tableau final
        $knockoutRounds = [
            '16es de finale' => [],
            '8es de finale' => [],
            'Quarts de finale' => [],
            'Demi-finale' => [],
            'Match pour la 3e place' => [],
            'Finale' => []
        ];

        $knockoutGames = Game::where('season_id', $season->id)
            ->whereIn('round', array_keys($knockoutRounds))
            ->with(['homeTeam', 'awayTeam'])
            ->orderBy('id', 'asc')
            ->get();

        // 3.1 Détecter dynamiquement les matchs de 16es de finale attendant un 3e
        $gameIds = [];
        $allowed3rdGroups = [];
        foreach ($knockoutGames as $game) {
            if ($game->round === '16es de finale') {
                $awayPlaceholder = $game->awayTeam?->name ?? '';
                if (str_contains($awayPlaceholder, '3e Groupe')) {
                    // Supprimer "3e Groupe" pour éviter de capturer le 'G' de 'Groupe'
                    $cleanPlaceholder = str_replace(['3e Groupe', 'Groupe'], '', $awayPlaceholder);
                    preg_match_all('/[A-L]/', $cleanPlaceholder, $matches);
                    $groups = $matches[0] ?? [];
                    if (!empty($groups)) {
                        $gameIds[] = $game->id;
                        $allowed3rdGroups[$game->id] = $groups;
                    }
                }
            }
        }

        // Assigner les 3es aux matchs de 16es de finale (backtracking + fallback)
        $matched3rds = $this->match3rdPlacedTeams($best3rds, $gameIds, $allowed3rdGroups) ?? [];

        // Fallback glouton si le backtracking ne trouve pas de correspondance parfaite
        if (count($matched3rds) < count($best3rds)) {
            $assignedTeamIds = collect($matched3rds)->pluck('id')->toArray();
            $remainingTeams = collect($best3rds)->filter(fn($t) => !in_array($t['team']->id, $assignedTeamIds));

            foreach ($gameIds as $gameId) {
                if (!isset($matched3rds[$gameId]) && $remainingTeams->isNotEmpty()) {
                    $matched3rds[$gameId] = $remainingTeams->shift()['team'];
                }
            }
        }

        $matchNumberToApiId = [
            73 => 537417, 74 => 537423, 75 => 537415, 76 => 537418,
            77 => 537424, 78 => 537416, 79 => 537425, 80 => 537426,
            81 => 537422, 82 => 537421, 83 => 537420, 84 => 537419,
            85 => 537429, 86 => 537428, 87 => 537427, 88 => 537430,
            89 => 537376, 90 => 537375, 91 => 537377, 92 => 537378,
            93 => 537379, 94 => 537380, 95 => 537381, 96 => 537382,
            97 => 537383, 98 => 537384, 99 => 537385, 100 => 537386,
            101 => 537387, 102 => 537388, 103 => 537389, 104 => 537390
        ];

        $gamesByApiId = $knockoutGames->keyBy('api_id');
        $resolvedCache = [];

        // Préréglage des 16es de finale dans les relations pour que la résolution récursive fonctionne
        foreach ($knockoutGames as $game) {
            if ($game->round === '16es de finale') {
                // Résoudre l'équipe à domicile
                $homePlaceholder = $game->homeTeam?->name;
                if ($homePlaceholder) {
                    if (str_starts_with($homePlaceholder, '1er Groupe ')) {
                        $groupLetter = substr($homePlaceholder, -1);
                        if (isset($winners[$groupLetter])) {
                            $game->setRelation('homeTeam', $winners[$groupLetter]);
                        }
                    } elseif (str_starts_with($homePlaceholder, '2e Groupe ')) {
                        $groupLetter = substr($homePlaceholder, -1);
                        if (isset($runnersUp[$groupLetter])) {
                            $game->setRelation('homeTeam', $runnersUp[$groupLetter]);
                        }
                    }
                }

                // Résoudre l'équipe à l'extérieur
                $awayPlaceholder = $game->awayTeam?->name;
                if ($awayPlaceholder) {
                    if (str_starts_with($awayPlaceholder, '1er Groupe ')) {
                        $groupLetter = substr($awayPlaceholder, -1);
                        if (isset($winners[$groupLetter])) {
                            $game->setRelation('awayTeam', $winners[$groupLetter]);
                        }
                    } elseif (str_starts_with($awayPlaceholder, '2e Groupe ')) {
                        $groupLetter = substr($awayPlaceholder, -1);
                        if (isset($runnersUp[$groupLetter])) {
                            $game->setRelation('awayTeam', $runnersUp[$groupLetter]);
                        }
                    } elseif (str_contains($awayPlaceholder, '3e Groupe')) {
                        if (isset($matched3rds[$game->id])) {
                            $game->setRelation('awayTeam', $matched3rds[$game->id]);
                        }
                    }
                }
            }
        }

        // Résoudre de manière récursive tous les matchs (des 8es à la Finale)
        foreach ($knockoutGames as $game) {
            $resolvedHome = $this->resolveTeam($game->homeTeam, $winners, $runnersUp, $matched3rds, $gamesByApiId, $matchNumberToApiId, $resolvedCache);
            if ($resolvedHome) {
                $game->setRelation('homeTeam', $resolvedHome);
            }

            $resolvedAway = $this->resolveTeam($game->awayTeam, $winners, $runnersUp, $matched3rds, $gamesByApiId, $matchNumberToApiId, $resolvedCache);
            if ($resolvedAway) {
                $game->setRelation('awayTeam', $resolvedAway);
            }

            $knockoutRounds[$game->round][] = $game;
        }

        $userChampionPrediction = ChampionPrediction::where('user_id', auth()->id())
            ->where('season_id', $season->id)
            ->with('team')
            ->first();

        $deadline = Carbon::create(2026, 6, 20, 23, 59, 59, 'Europe/Paris');
        $championDeadlinePassed = Carbon::now('Europe/Paris')->greaterThan($deadline);

        $wcTeams = Team::where(function ($query) use ($season) {
            $query->whereHas('homeGames', function ($q) use ($season) {
                $q->where('season_id', $season->id);
            })->orWhereHas('awayGames', function ($q) use ($season) {
                $q->where('season_id', $season->id);
            });
        })
        ->where('name', 'not like', '%À déterminer%')
        ->where('name', 'not like', '%Groupe%')
        ->where('name', 'not like', '%Vainqueur%')
        ->where('name', 'not like', '%Perdant%')
        ->orderBy('name')
        ->get();

        return view('standings.worldcup', compact(
            'season',
            'groupStandings',
            'knockoutRounds',
            'userChampionPrediction',
            'championDeadlinePassed',
            'wcTeams'
        ));
    }

    private function resolveTeam($team, $winners, $runnersUp, $matched3rds, $gamesByApiId, $matchNumberToApiId, &$resolvedCache = [])
    {
        if (!$team) {
            return null;
        }

        $name = $team->name;

        // Si c'est déjà une vraie équipe (pas de termes de placeholder), on la retourne telle quelle
        $placeholderTerms = ['1er Groupe', '2e Groupe', '3e Groupe', 'Vainqueur Match', 'Perdant Match'];
        $isPlaceholder = false;
        foreach ($placeholderTerms as $term) {
            if (str_contains($name, $term)) {
                $isPlaceholder = true;
                break;
            }
        }

        if (!$isPlaceholder) {
            return $team;
        }

        // Éviter les boucles infinies
        if (isset($resolvedCache[$name])) {
            return $resolvedCache[$name];
        }
        $resolvedCache[$name] = null; // Marquer comme en cours

        // 1. Résolution des vainqueurs de groupe
        if (str_starts_with($name, '1er Groupe ')) {
            $groupLetter = substr($name, -1);
            $resolved = $winners[$groupLetter] ?? null;
            $resolvedCache[$name] = $resolved;
            return $resolved;
        }

        // 2. Résolution des deuxièmes de groupe
        if (str_starts_with($name, '2e Groupe ')) {
            $groupLetter = substr($name, -1);
            $resolved = $runnersUp[$groupLetter] ?? null;
            $resolvedCache[$name] = $resolved;
            return $resolved;
        }

        // 3. Résolution des troisièmes de groupe
        if (str_contains($name, '3e Groupe')) {
            return null; 
        }

        // 4. Résolution des vainqueurs/perdants de matchs
        if (preg_match('/(Vainqueur|Perdant) Match (\d+)/', $name, $matches)) {
            $type = $matches[1]; // Vainqueur ou Perdant
            $matchNum = (int)$matches[2];

            $apiId = $matchNumberToApiId[$matchNum] ?? null;
            if (!$apiId) {
                return null;
            }

            $sourceGame = $gamesByApiId[$apiId] ?? null;
            if (!$sourceGame) {
                return null;
            }

            // Si le match source a déjà ses vraies équipes résolues de manière transitive, on les résout d'abord
            $sourceHome = $this->resolveTeam($sourceGame->homeTeam, $winners, $runnersUp, $matched3rds, $gamesByApiId, $matchNumberToApiId, $resolvedCache);
            $sourceAway = $this->resolveTeam($sourceGame->awayTeam, $winners, $runnersUp, $matched3rds, $gamesByApiId, $matchNumberToApiId, $resolvedCache);

            if ($sourceGame->is_finished && $sourceHome && $sourceAway) {
                if ($sourceGame->home_score > $sourceGame->away_score) {
                    $winner = $sourceHome;
                    $loser = $sourceAway;
                } elseif ($sourceGame->away_score > $sourceGame->home_score) {
                    $winner = $sourceAway;
                    $loser = $sourceHome;
                } else {
                    return null;
                }

                $resolved = ($type === 'Vainqueur') ? $winner : $loser;
                $resolvedCache[$name] = $resolved;
                return $resolved;
            }
        }

        return null;
    }

    private function match3rdPlacedTeams($best3rds, $gameIds, $allowed3rdGroups, $index = 0, $currentMatch = [])
    {
        if ($index >= count($best3rds)) {
            return $currentMatch;
        }

        $thirdTeam = $best3rds[$index];
        $group = $thirdTeam['group'];

        foreach ($gameIds as $gameId) {
            if (isset($currentMatch[$gameId])) {
                continue;
            }

            if (in_array($group, $allowed3rdGroups[$gameId])) {
                $currentMatch[$gameId] = $thirdTeam['team'];
                $result = $this->match3rdPlacedTeams($best3rds, $gameIds, $allowed3rdGroups, $index + 1, $currentMatch);
                if ($result !== null) {
                    return $result;
                }
                unset($currentMatch[$gameId]);
            }
        }

        return null;
    }

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
