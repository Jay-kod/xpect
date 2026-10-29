<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SyncRun extends Model
{
    protected $table = 'sync_runs';

    protected $guarded = [];

    protected $casts = [
        'arguments' => 'array',
        'summary' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function apiCalls(): HasMany
    {
        return $this->hasMany(ApiCall::class, 'sync_run_id');
    }
}

