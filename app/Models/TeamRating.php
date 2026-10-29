<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamRating extends Model
{
    protected $table = 'team_ratings';

    protected $guarded = [];

    protected $casts = [
        'rating' => 'decimal:2',
        'as_of' => 'date',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id');
    }
}

