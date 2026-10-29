<?php

declare(strict_types=1);

/**
 * config/api_catalog.php - Machine copy of API_CATALOG.md
 * 
 * Defines all permitted outbound endpoints, their HTTP method, verification status,
 * and description. The inspector reads this file to validate and test endpoints.
 */

return [
    'sources' => [
        'football_data' => [
            'name' => 'football-data.org',
            'base_url' => 'https://api.football-data.org/v4',
            'auth_header' => 'X-Auth-Token',
            'endpoints' => [
                [
                    'method' => 'GET',
                    'path' => '/competitions',
                    'purpose' => 'Competitions available on your plan',
                    'status' => 'DOC',
                ],
                [
                    'method' => 'GET',
                    'path' => '/competitions/{code}',
                    'purpose' => 'Competition detail and seasons',
                    'status' => 'DOC',
                ],
                [
                    'method' => 'GET',
                    'path' => '/competitions/{code}/matches',
                    'purpose' => 'Fixtures and results for a competition and season',
                    'status' => 'VERIFIED',
                    'verified_at' => '2026-10-28',
                    'notes' => 'Confirmed with PL. Free tier supports 2026 and 2023; <=2022 returns 403 Forbidden.',
                ],
                [
                    'method' => 'GET',
                    'path' => '/competitions/{code}/standings',
                    'purpose' => 'League table (cross-check only; we compute our own)',
                    'status' => 'DOC',
                ],
                [
                    'method' => 'GET',
                    'path' => '/competitions/{code}/teams',
                    'purpose' => 'Teams in a season',
                    'status' => 'DOC',
                ],
                [
                    'method' => 'GET',
                    'path' => '/teams/{id}',
                    'purpose' => 'Team detail',
                    'status' => 'DOC',
                ],
            ],
        ],

        'api_football' => [
            'name' => 'API-Football (api-sports)',
            'base_url' => 'https://v3.football.api-sports.io',
            'auth_header' => 'x-apisports-key',
            'endpoints' => [
                [
                    'method' => 'GET',
                    'path' => '/status',
                    'purpose' => 'Plan and quota usage',
                    'status' => 'DOC',
                ],
                [
                    'method' => 'GET',
                    'path' => '/leagues',
                    'purpose' => 'League list and coverage',
                    'status' => 'DOC',
                ],
                [
                    'method' => 'GET',
                    'path' => '/countries',
                    'purpose' => 'Country list',
                    'status' => 'DOC',
                ],
                [
                    'method' => 'GET',
                    'path' => '/teams',
                    'purpose' => 'Team lookup',
                    'status' => 'DOC',
                ],
                [
                    'method' => 'GET',
                    'path' => '/fixtures',
                    'purpose' => 'Fixtures by league, season, or filters',
                    'status' => 'DOC',
                ],
                [
                    'method' => 'GET',
                    'path' => '/injuries',
                    'purpose' => 'Injuries by league and season',
                    'status' => 'MEM',
                ],
                [
                    'method' => 'GET',
                    'path' => '/fixtures/lineups',
                    'purpose' => 'Lineups for a fixture',
                    'status' => 'MEM',
                ],
                [
                    'method' => 'GET',
                    'path' => '/standings',
                    'purpose' => 'League table',
                    'status' => 'MEM',
                ],
                [
                    'method' => 'GET',
                    'path' => '/fixtures/statistics',
                    'purpose' => 'Match stats',
                    'status' => 'MEM',
                ],
            ],
        ],

        'csv' => [
            'name' => 'Football-Data.co.uk CSV',
            'base_url' => 'https://www.football-data.co.uk',
            'endpoints' => [
                [
                    'method' => 'GET',
                    'path' => '/mmz4281/{season}/{div}.csv',
                    'purpose' => 'Historical match results and odds CSV',
                    'status' => 'VERIFIED',
                    'verified_at' => '2026-10-28',
                    'notes' => 'Confirmed across seasons 2020/21 - 2024/25 for E0, SP1, D1, I1, F1. Date format DD/MM/YYYY.',
                ],
            ],
        ],

        'anthropic' => [
            'name' => 'Anthropic Claude API',
            'base_url' => 'https://api.anthropic.com/v1',
            'auth_header' => 'x-api-key',
            'endpoints' => [
                [
                    'method' => 'POST',
                    'path' => '/messages',
                    'purpose' => 'Generate AI match preview from structured numbers',
                    'status' => 'DOC',
                ],
            ],
        ],

        'understat' => [
            'name' => 'Understat (xG for Top 5 Leagues)',
            'base_url' => 'https://understat.com',
            'endpoints' => [
                [
                    'method' => 'GET',
                    'path' => '/getLeagueData/{league}/{season}',
                    'purpose' => 'Clean JSON endpoint returning season match dates, results, and xG',
                    'status' => 'VERIFIED',
                    'verified_at' => '2026-10-28',
                    'notes' => 'Confirmed with EPL 2024. Returns JSON object with dates (380 matches, xG floats), teams, players.',
                ],
                [
                    'method' => 'GET',
                    'path' => '/league/{league}/{season}',
                    'purpose' => 'League season HTML page',
                    'status' => 'DOC',
                ],
                [
                    'method' => 'GET',
                    'path' => '/match/{id}',
                    'purpose' => 'Detailed single match xG and shot map data embedded in HTML',
                    'status' => 'DOC',
                ],
            ],
        ],
    ],
];

