<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
    use HasFactory;
    protected $fillable = [
        'season_id',
        'home_team_id',
        'away_team_id',
        'matchday',
        'match_date',
        'home_score',
        'away_score',
        'is_finished',
        'round',
        'group',
        'stadium',
    ];

    protected $casts = [
        'match_date' => 'datetime',
        'is_finished' => 'boolean',
    ];

    /**
     * Get and set the match date in Europe/Paris timezone.
     */
    protected function matchDate(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn ($value) => $value ? \Carbon\Carbon::parse($value, 'Europe/Paris') : null,
            set: fn ($value) => $value ? \Carbon\Carbon::parse($value)->setTimezone('Europe/Paris')->format('Y-m-d H:i:s') : null,
        );
    }

    public function isGroupStage(): bool
    {
        return $this->round === 'Phase de groupes';
    }

    public function isKnockout(): bool
    {
        return !$this->isGroupStage() && !is_null($this->round);
    }

    public function hasPlaceholderTeams(): bool
    {
        $placeholderTerms = ['À déterminer', 'Groupe', 'Vainqueur', 'Perdant'];
        foreach ($placeholderTerms as $term) {
            if (str_contains($this->homeTeam?->name ?? '', $term) || str_contains($this->awayTeam?->name ?? '', $term)) {
                return true;
            }
        }
        return false;
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function homeTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'home_team_id');
    }

    public function awayTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'away_team_id');
    }

    public function predictions(): HasMany
    {
        return $this->hasMany(Prediction::class);
    }
}
