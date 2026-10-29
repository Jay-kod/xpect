<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PredictionEvaluation extends Model
{
    protected $table = 'prediction_evaluations';

    protected $guarded = [];

    protected $casts = [
        'correct' => 'boolean',
        'log_loss' => 'decimal:4',
        'brier' => 'decimal:4',
        'evaluated_at' => 'datetime',
    ];

    public function prediction(): BelongsTo
    {
        return $this->belongsTo(Prediction::class, 'prediction_id');
    }
}

