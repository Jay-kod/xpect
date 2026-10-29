<?php

declare(strict_types=1);

namespace App\Services\DataSources\Contracts;

use App\Models\Competition;

interface DataSourceAdapter
{
    /**
     * Ingest matches for a competition and season.
     * Returns an array summary: ['imported' => int, 'skipped' => int, 'unknown_teams' => array]
     */
    public function importMatches(
        Competition $competition,
        int $season,
        bool $fromCache = false,
        ?int $syncRunId = null
    ): array;
}

