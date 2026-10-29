<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemAudit extends Model
{
    protected $fillable = [
        'triggered_by',
        'status',
        'total_checks',
        'checks_passed',
        'checks_failed',
        'checks_warning',
        'duration_ms',
        'summary',
        'report_json',
    ];

    protected $casts = [
        'total_checks' => 'integer',
        'checks_passed' => 'integer',
        'checks_failed' => 'integer',
        'checks_warning' => 'integer',
        'duration_ms' => 'float',
        'report_json' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}

