<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamAlias extends Model
{
    protected $table = 'team_aliases';

    protected $guarded = [];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id');
    }
}

