<?php

declare(strict_types=1);

namespace App\Services\Teams;

use App\Models\TeamAlias;
use RuntimeException;

class TeamResolver
{
    /**
     * In-memory cache for fast resolution during large historical backfills: [source][alias] => team_id
     */
    protected array $cache = [];

    /**
     * Resolve a source-specific team name string to a canonical team_id.
     * Returns null if unmapped (never auto-creates).
     */
    public function resolve(string $source, string $alias): ?int
    {
        $trimmed = trim($alias);
        if ($trimmed === '') {
            return null;
        }

        if (isset($this->cache[$source][$trimmed])) {
            return $this->cache[$source][$trimmed];
        }

        $teamAlias = TeamAlias::where('source', $source)
            ->where('alias', $trimmed)
            ->first();

        if ($teamAlias) {
            $this->cache[$source][$trimmed] = $teamAlias->team_id;
            return $teamAlias->team_id;
        }

        return null;
    }

    /**
     * Resolve or throw an exception if unknown.
     */
    public function resolveOrFail(string $source, string $alias): int
    {
        $id = $this->resolve($source, $alias);

        if ($id === null) {
            throw new RuntimeException("Unknown team name [{$alias}] for source [{$source}]. Must be added to team_aliases.");
        }

        return $id;
    }

    /**
     * Clear in-memory cache.
     */
    public function clearCache(): void
    {
        $this->cache = [];
    }
}

