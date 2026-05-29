<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChampionPrediction extends Model
{
    use HasFactory;

    const BONUS_POINTS = 30;

    protected $fillable = [
        'user_id',
        'season_id',
        'team_id',
    ];

    public function isCorrect(Season $season): bool
    {
        return $this->team_id !== null
            && $season->winner_team_id !== null
            && $this->team_id === $season->winner_team_id;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
