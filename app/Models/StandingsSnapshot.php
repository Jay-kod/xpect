<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StandingsSnapshot extends Model
{
    protected $table = 'standings_snapshots';

    protected $guarded = [];

    protected $casts = [
        'season' => 'integer',
        'matchday' => 'integer',
        'position' => 'integer',
        'played' => 'integer',
        'won' => 'integer',
        'drawn' => 'integer',
        'lost' => 'integer',
        'goals_for' => 'integer',
        'goals_against' => 'integer',
        'points' => 'integer',
        'as_of' => 'datetime',
    ];

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class, 'competition_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id');
    }
}

