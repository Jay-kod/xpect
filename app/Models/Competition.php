<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Competition extends Model
{
    protected $table = 'competitions';

    protected $guarded = [];

    protected $casts = [
        'source_ids' => 'array',
        'is_active' => 'boolean',
    ];

    public function matches(): HasMany
    {
        return $this->hasMany(FootballMatch::class, 'competition_id');
    }

    public function standingsSnapshots(): HasMany
    {
        return $this->hasMany(StandingsSnapshot::class, 'competition_id');
    }
}

