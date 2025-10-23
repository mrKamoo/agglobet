<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'short_name',
        'logo',
        'city',
        'stadium',
    ];

    public function homeGames(): HasMany
    {
        return $this->hasMany(Game::class, 'home_team_id');
    }

    public function awayGames(): HasMany
    {
        return $this->hasMany(Game::class, 'away_team_id');
    }



    /**
     * Get the last 5 finished games for this team
     * Returns an array of results: 'W' (win), 'D' (draw), 'L' (loss)
     */
    public function getLastFiveGames($beforeGameId = null): array
    {
        $query = Game::where(function ($q) {
                $q->where('home_team_id', $this->id)
                  ->orWhere('away_team_id', $this->id);
            })
            ->where('is_finished', true)
            ->orderBy('match_date', 'desc');

        // Exclude the current game if beforeGameId is provided
        if ($beforeGameId) {
            $query->where('id', '!=', $beforeGameId);
        }

        $games = $query->limit(5)->get();

        return $games->map(function ($game) {
            $isHome = $game->home_team_id === $this->id;
            $teamScore = $isHome ? $game->home_score : $game->away_score;
            $opponentScore = $isHome ? $game->away_score : $game->home_score;

            if ($teamScore > $opponentScore) {
                return 'W'; // Win
            } elseif ($teamScore < $opponentScore) {
                return 'L'; // Loss
            } else {
                return 'D'; // Draw
            }
        })->toArray();
    }
}
