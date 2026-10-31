<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Feature & Statistical Model Parameters
    |--------------------------------------------------------------------------
    */
    'features' => [
        'window_matches' => (int) env('FOOTBALL_WINDOW_MATCHES', 20),
        'half_life_days' => (int) env('FOOTBALL_HALF_LIFE_DAYS', 60),
        'shrinkage_k' => (float) env('FOOTBALL_SHRINKAGE_K', 3.0),
    ],

    /*
    |--------------------------------------------------------------------------
    | Advanced Model Parameters
    |--------------------------------------------------------------------------
    */
    'models' => [
        'dixon_coles_rho' => (float) env('DIXON_COLES_RHO', -0.06),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache TTL Guidance (in seconds)
    |--------------------------------------------------------------------------
    */
    'cache_ttl' => [
        'finished_season' => null, // null = forever
        'current_fixtures' => 12 * 3600, // 12 hours
        'matchday_results' => 15 * 60,   // 15 minutes
        'llm' => 0,                      // LLM calls never cached automatically
    ],

    /*
    |--------------------------------------------------------------------------
    | Outbound HTTP Quotas and Rate Limits
    |--------------------------------------------------------------------------
    */
    'quotas' => [
        'football_data' => [
            'per_minute' => 10,
            'per_day' => null,
            'reserve' => 0,
        ],
        'api_football' => [
            'per_minute' => 10,
            'per_day' => 100,
            'reserve' => 10,
        ],
        'understat' => [
            'per_minute' => 20,
            'per_day' => null,
            'reserve' => 0,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Header & Parameter Keys Redaction List
    |--------------------------------------------------------------------------
    | Headers or query parameters matching these names will be masked BEFORE
    | being written to the api_calls table or application logs.
    */
    'secrets' => [
        'Authorization',
        'X-Auth-Token',
        'x-apisports-key',
        'x-api-key',
        'x-goog-api-key',
        'api_key',
        'token',
        'secret',
    ],

    /*
    |--------------------------------------------------------------------------
    | SQLite Backups
    |--------------------------------------------------------------------------
    */
    'backups' => [
        'keep_count' => (int) env('FOOTBALL_BACKUP_KEEP_COUNT', 10),
    ],
];

