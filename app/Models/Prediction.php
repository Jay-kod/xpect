<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use RuntimeException;

class Prediction extends Model
{
    protected $table = 'predictions';

    protected $guarded = [];

    protected $casts = [
        'is_backtest' => 'boolean',
        'low_confidence' => 'boolean',
        'as_of' => 'datetime',
        'p_home' => 'decimal:4',
        'p_draw' => 'decimal:4',
        'p_away' => 'decimal:4',
        'exp_home_goals' => 'decimal:2',
        'exp_away_goals' => 'decimal:2',
        'top_scorelines' => 'array',
        'features' => 'array',
    ];

    protected static function booted(): void
    {
        static::updating(function () {
            throw new RuntimeException('predictions rows are immutable.');
        });
        static::deleting(function () {
            throw new RuntimeException('predictions rows are immutable.');
        });
    }

    public function match(): BelongsTo
    {
        return $this->belongsTo(FootballMatch::class, 'match_id');
    }

    public function evaluation(): HasOne
    {
        return $this->hasOne(PredictionEvaluation::class, 'prediction_id');
    }

    public function previews(): HasMany
    {
        return $this->hasMany(Preview::class, 'prediction_id');
    }
}

