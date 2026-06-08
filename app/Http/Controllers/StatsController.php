<?php

namespace App\Http\Controllers;

use App\Models\Season;
use App\Models\Prediction;
use App\Models\ChampionPrediction;
use App\Models\PointsRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    public function index(Request $request)
    {
        $seasons = Season::orderBy('start_date', 'desc')->get();
        $selectedSeasonId = $request->get('season_id', Season::where('is_active', true)->first()?->id ?? $seasons->first()?->id);
        $selectedSeason = Season::find($selectedSeasonId);

        $pointsRule = PointsRule::where('is_active', true)->first();
        $exactScorePoints = $pointsRule?->exact_score ?? 5;
        $correctDifferencePoints = $pointsRule?->correct_difference ?? 3;
        $correctWinnerPoints = $pointsRule?->correct_winner ?? 1;

        // Base query for finished predicted games
        $predictionsQuery = Prediction::query()
            ->join('games', 'predictions.game_id', '=', 'games.id')
            ->where('games.is_finished', true);

        if ($selectedSeasonId) {
            $predictionsQuery->where('games.season_id', $selectedSeasonId);
        }

        // KPIs
        $totalPredictions = (clone $predictionsQuery)->count();
        $totalPoints = (clone $predictionsQuery)->sum('predictions.points_earned');
        $avgPoints = $totalPredictions > 0 ? round($totalPoints / $totalPredictions, 2) : 0;
        
        $successfulPredictions = (clone $predictionsQuery)->where('predictions.points_earned', '>', 0)->count();
        $successRate = $totalPredictions > 0 ? round(($successfulPredictions / $totalPredictions) * 100, 1) : 0;

        // Points distribution
        $exactScoresCount = (clone $predictionsQuery)->where('predictions.points_earned', $exactScorePoints)->count();
        $correctDifferencesCount = (clone $predictionsQuery)->where('predictions.points_earned', $correctDifferencePoints)->count();
        $correctWinnersCount = (clone $predictionsQuery)->where('predictions.points_earned', $correctWinnerPoints)->count();
        $incorrectCount = (clone $predictionsQuery)->where('predictions.points_earned', 0)->count();

        // Predictions distribution (Home / Draw / Away)
        $homeWinsPredicted = (clone $predictionsQuery)->whereRaw('predictions.home_score > predictions.away_score')->count();
        $drawsPredicted = (clone $predictionsQuery)->whereRaw('predictions.home_score = predictions.away_score')->count();
        $awayWinsPredicted = (clone $predictionsQuery)->whereRaw('predictions.home_score < predictions.away_score')->count();

        // Actual outcomes distribution for predicted games
        $homeWinsActual = (clone $predictionsQuery)->whereRaw('games.home_score > games.away_score')->count();
        $drawsActual = (clone $predictionsQuery)->whereRaw('games.home_score = games.away_score')->count();
        $awayWinsActual = (clone $predictionsQuery)->whereRaw('games.home_score < games.away_score')->count();

        // Top predicted wins per team
        $homeWinsQuery = DB::table('predictions')
            ->join('games', 'predictions.game_id', '=', 'games.id')
            ->where('games.is_finished', true)
            ->when($selectedSeasonId, function($q) use ($selectedSeasonId) {
                $q->where('games.season_id', $selectedSeasonId);
            })
            ->whereRaw('predictions.home_score > predictions.away_score')
            ->select('games.home_team_id as team_id', DB::raw('count(*) as count'))
            ->groupBy('games.home_team_id');

        $awayWinsQuery = DB::table('predictions')
            ->join('games', 'predictions.game_id', '=', 'games.id')
            ->where('games.is_finished', true)
            ->when($selectedSeasonId, function($q) use ($selectedSeasonId) {
                $q->where('games.season_id', $selectedSeasonId);
            })
            ->whereRaw('predictions.home_score < predictions.away_score')
            ->select('games.away_team_id as team_id', DB::raw('count(*) as count'))
            ->groupBy('games.away_team_id');

        $mostPredictedWins = DB::table(DB::raw("({$homeWinsQuery->toSql()} UNION ALL {$awayWinsQuery->toSql()}) as wins"))
            ->mergeBindings($homeWinsQuery)
            ->mergeBindings($awayWinsQuery)
            ->join('teams', 'wins.team_id', '=', 'teams.id')
            ->select('teams.id', 'teams.name', 'teams.short_name', 'teams.logo', DB::raw('SUM(count) as win_prediction_count'))
            ->groupBy('teams.id', 'teams.name', 'teams.short_name', 'teams.logo')
            ->orderByDesc('win_prediction_count')
            ->limit(5)
            ->get();

        // Top predicted losses per team
        $homeLossesQuery = DB::table('predictions')
            ->join('games', 'predictions.game_id', '=', 'games.id')
            ->where('games.is_finished', true)
            ->when($selectedSeasonId, function($q) use ($selectedSeasonId) {
                $q->where('games.season_id', $selectedSeasonId);
            })
            ->whereRaw('predictions.home_score < predictions.away_score')
            ->select('games.home_team_id as team_id', DB::raw('count(*) as count'))
            ->groupBy('games.home_team_id');

        $awayLossesQuery = DB::table('predictions')
            ->join('games', 'predictions.game_id', '=', 'games.id')
            ->where('games.is_finished', true)
            ->when($selectedSeasonId, function($q) use ($selectedSeasonId) {
                $q->where('games.season_id', $selectedSeasonId);
            })
            ->whereRaw('predictions.home_score > predictions.away_score')
            ->select('games.away_team_id as team_id', DB::raw('count(*) as count'))
            ->groupBy('games.away_team_id');

        $mostPredictedLosses = DB::table(DB::raw("({$homeLossesQuery->toSql()} UNION ALL {$awayLossesQuery->toSql()}) as losses"))
            ->mergeBindings($homeLossesQuery)
            ->mergeBindings($awayLossesQuery)
            ->join('teams', 'losses.team_id', '=', 'teams.id')
            ->select('teams.id', 'teams.name', 'teams.short_name', 'teams.logo', DB::raw('SUM(count) as loss_prediction_count'))
            ->groupBy('teams.id', 'teams.name', 'teams.short_name', 'teams.logo')
            ->orderByDesc('loss_prediction_count')
            ->limit(5)
            ->get();

        // Champion Predictions stats
        $championStats = collect();
        if ($selectedSeasonId) {
            $championStats = ChampionPrediction::where('season_id', $selectedSeasonId)
                ->join('teams', 'champion_predictions.team_id', '=', 'teams.id')
                ->select('teams.id', 'teams.name', 'teams.short_name', 'teams.logo', DB::raw('count(*) as count'))
                ->groupBy('teams.id', 'teams.name', 'teams.short_name', 'teams.logo')
                ->orderByDesc('count')
                ->get();
        }

        return view('stats.index', compact(
            'seasons',
            'selectedSeasonId',
            'selectedSeason',
            'totalPredictions',
            'totalPoints',
            'avgPoints',
            'successRate',
            'exactScoresCount',
            'correctDifferencesCount',
            'correctWinnersCount',
            'incorrectCount',
            'homeWinsPredicted',
            'drawsPredicted',
            'awayWinsPredicted',
            'homeWinsActual',
            'drawsActual',
            'awayWinsActual',
            'mostPredictedWins',
            'mostPredictedLosses',
            'championStats',
            'pointsRule'
        ));
    }
}
