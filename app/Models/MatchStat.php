<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchStat extends Model
{
    protected $table = 'match_stats';

    protected $guarded = [];

    protected $casts = [
        'xg' => 'float',
        'xga' => 'float',
        'shots' => 'integer',
        'shots_on_target' => 'integer',
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
