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

    /**
     * Compute clean 2-3 letter initials for the fallback badge shield.
     */
    public function getInitialsAttribute(): string
    {
        if (!empty($this->short_name)) {
            $words = array_values(array_filter(explode(' ', trim($this->short_name))));
            if (count($words) >= 2) {
                return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
            }
            return strtoupper(substr($this->short_name, 0, min(3, strlen($this->short_name))));
        }

        $clean = preg_replace('/\b(FC|AFC|CF|SC|SSC|SV|VfB|VfL|AS)\b/i', '', $this->name ?? '');
        $words = array_values(array_filter(explode(' ', trim((string) $clean))));
        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        }
        return strtoupper(substr(trim((string) $clean), 0, min(3, strlen(trim((string) $clean)))));
    }
}

