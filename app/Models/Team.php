<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    protected $table = 'teams';

    protected $guarded = [];

    public function aliases(): HasMany
    {
        return $this->hasMany(TeamAlias::class, 'team_id');
    }

    public function homeMatches(): HasMany
    {
        return $this->hasMany(FootballMatch::class, 'home_team_id');
    }

    public function awayMatches(): HasMany
    {
        return $this->hasMany(FootballMatch::class, 'away_team_id');
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(TeamRating::class, 'team_id');
    }

    public function injuries(): HasMany
    {
        return $this->hasMany(Injury::class, 'team_id');
    }
}

