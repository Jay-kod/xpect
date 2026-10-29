<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;

class ApiCall extends Model
{
    protected $table = 'api_calls';

    protected $guarded = [];

    protected $casts = [
        'request_params' => 'array',
        'request_headers' => 'array',
        'response_headers' => 'array',
        'counts_toward_quota' => 'boolean',
        'served_from_cache' => 'boolean',
        'called_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::updating(function () {
            throw new RuntimeException('api_calls rows are immutable.');
        });
        static::deleting(function () {
            throw new RuntimeException('api_calls rows are immutable.');
        });
    }

    public function cachedFrom(): BelongsTo
    {
        return $this->belongsTo(self::class, 'cached_from_call_id');
    }

    public function syncRun(): BelongsTo
    {
        return $this->belongsTo(SyncRun::class, 'sync_run_id');
    }
}

