<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FootballMatch extends Model
{
    protected $table = 'matches';

    protected $guarded = [];

    protected $casts = [
        'kickoff_at' => 'datetime',
        'season' => 'integer',
        'matchday' => 'integer',
        'home_goals' => 'integer',
        'away_goals' => 'integer',
        'ht_home_goals' => 'integer',
        'ht_away_goals' => 'integer',
    ];

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class, 'competition_id');
    }

    public function homeTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'home_team_id');
    }

    public function awayTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'away_team_id');
    }

    public function sourceApiCall(): BelongsTo
    {
        return $this->belongsTo(ApiCall::class, 'source_api_call_id');
    }

    public function predictions(): HasMany
    {
        return $this->hasMany(Prediction::class, 'match_id');
    }

    public function stats(): HasMany
    {
        return $this->hasMany(MatchStat::class, 'match_id');
    }

    public function lineups(): HasMany
    {
        return $this->hasMany(MatchLineup::class, 'match_id');
    }

    public function odds(): HasMany
    {
        return $this->hasMany(MatchOdds::class, 'match_id');
    }

    public function motmEvaluations(): HasMany
    {
        return $this->hasMany(MotmEvaluation::class, 'match_id');
    }
}

