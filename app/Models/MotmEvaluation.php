<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MotmEvaluation extends Model
{
    protected $table = 'motm_evaluations';

    protected $guarded = [];

    protected $casts = [
        'is_starter' => 'boolean',
        'is_motm' => 'boolean',
        'rating' => 'float',
        'data_rating' => 'float',
        'decision_score' => 'float',
        'goals' => 'integer',
        'assists' => 'integer',
        'xg' => 'float',
        'decisive_actions' => 'array',
    ];

    public function match(): BelongsTo
    {
        return $this->belongsTo(FootballMatch::class, 'match_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    public function sourceApiCall(): BelongsTo
    {
        return $this->belongsTo(ApiCall::class, 'source_api_call_id');
    }
}

