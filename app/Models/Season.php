<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Season extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_active',
        'type',
        'competition_id',
        'winner_team_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'competition_id' => 'integer',
        'winner_team_id' => 'integer',
    ];

    /** Indique si la saison est un tournoi (ex: Coupe du Monde) */
    public function isTournament(): bool
    {
        return $this->type === 'tournament';
    }

    /** Indique si la saison est un championnat (ex: Ligue 1) */
    public function isLeague(): bool
    {
        return $this->type === 'league';
    }

    public function games(): HasMany
    {
        return $this->hasMany(Game::class);
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'winner_team_id');
    }

    public function championPredictions(): HasMany
    {
        return $this->hasMany(ChampionPrediction::class);
    }
}
