<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchOdds extends Model
{
    protected $table = 'match_odds';

    protected $fillable = [
        'match_id',
        'bookmaker',
        'home_odds',
        'draw_odds',
        'away_odds',
        'implied_home_prob',
        'implied_draw_prob',
        'implied_away_prob',
        'overround',
        'source',
        'source_api_call_id',
    ];

    protected $casts = [
        'home_odds' => 'float',
        'draw_odds' => 'float',
        'away_odds' => 'float',
        'implied_home_prob' => 'float',
        'implied_draw_prob' => 'float',
        'implied_away_prob' => 'float',
        'overround' => 'float',
    ];

    public function match(): BelongsTo
    {
        return $this->belongsTo(FootballMatch::class, 'match_id');
    }

    public function apiCall(): BelongsTo
    {
        return $this->belongsTo(ApiCall::class, 'source_api_call_id');
    }
}
