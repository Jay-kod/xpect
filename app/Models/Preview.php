<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Preview extends Model
{
    protected $table = 'previews';

    protected $guarded = [];

    protected $casts = [
        'flagged' => 'boolean',
    ];

    public function prediction(): BelongsTo
    {
        return $this->belongsTo(Prediction::class, 'prediction_id');
    }

    public function apiCall(): BelongsTo
    {
        return $this->belongsTo(ApiCall::class, 'api_call_id');
    }
}

