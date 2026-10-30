<?php

declare(strict_types=1);

namespace App\Services\Teams;

use App\Models\Team;
use Illuminate\Support\Facades\File;

class TeamBadgeService
{
    /**
     * Cache of fixtures-discovered crests.
     *
     * @var array<string, string>|null
     */
    private ?array $fixtureCrests = null;

    /**
     * Resolve the crest URL for a team.
     */
    public function resolveCrest(Team $team): ?string
    {
        if (!empty($team->crest_url)) {
            return $team->crest_url;
        }

        // 1. Try registry by team name
        if ($crest = TeamBadgeRegistry::resolve($team->name)) {
            return $crest;
        }

        // 2. Try registry by short_name
        if (!empty($team->short_name) && ($crest = TeamBadgeRegistry::resolve($team->short_name))) {
            return $crest;
        }

        // 3. Try aliases
        if ($team->relationLoaded('aliases') || $team->aliases()->exists()) {
            foreach ($team->aliases as $alias) {
                if ($crest = TeamBadgeRegistry::resolve($alias->alias)) {
                    return $crest;
                }
            }
        }

        // 4. Try local fixtures
        $fixtureCrests = $this->getFixtureCrests();
        $normName = TeamBadgeRegistry::normalize($team->name);
        if (isset($fixtureCrests[$normName])) {
            return $fixtureCrests[$normName];
        }

        if (!empty($team->short_name)) {
            $normShort = TeamBadgeRegistry::normalize($team->short_name);
            if (isset($fixtureCrests[$normShort])) {
                return $fixtureCrests[$normShort];
            }
        }

        return null;
    }

    /**
     * Synchronize badges for all teams in the database.
     *
     * @return int Number of teams updated
     */
    public function syncBadges(bool $force = false): int
    {
        $teams = Team::with('aliases')->get();
        $updated = 0;

        foreach ($teams as $team) {
            if (!$force && !empty($team->crest_url)) {
                continue;
            }

            $crest = $this->resolveCrest($team);

            if ($crest !== null && $crest !== $team->crest_url) {
                $team->update(['crest_url' => $crest]);
                $updated++;
            }
        }

        return $updated;
    }

    /**
     * Extract team crests from local fixture files.
     *
     * @return array<string, string>
     */
    public function getFixtureCrests(): array
    {
        if ($this->fixtureCrests !== null) {
            return $this->fixtureCrests;
        }

        $this->fixtureCrests = [];
        $fixturesDir = base_path('tests/Fixtures');

        if (!File::isDirectory($fixturesDir)) {
            return $this->fixtureCrests;
        }

        $files = [
            $fixturesDir . '/football_data_pl_matches.json',
            $fixturesDir . '/football_data_pl_2023.json',
        ];

        foreach ($files as $filePath) {
            if (!File::exists($filePath)) {
                continue;
            }

            try {
                $content = File::get($filePath);
                $data = json_decode($content, true);

                if (!isset($data['matches']) || !is_array($data['matches'])) {
                    continue;
                }

                foreach ($data['matches'] as $match) {
                    if (isset($match['homeTeam']['name'], $match['homeTeam']['crest'])) {
                        $norm = TeamBadgeRegistry::normalize($match['homeTeam']['name']);
                        $this->fixtureCrests[$norm] = $match['homeTeam']['crest'];
                        if (isset($match['homeTeam']['shortName'])) {
                            $this->fixtureCrests[TeamBadgeRegistry::normalize($match['homeTeam']['shortName'])] = $match['homeTeam']['crest'];
                        }
                    }

                    if (isset($match['awayTeam']['name'], $match['awayTeam']['crest'])) {
                        $norm = TeamBadgeRegistry::normalize($match['awayTeam']['name']);
                        $this->fixtureCrests[$norm] = $match['awayTeam']['crest'];
                        if (isset($match['awayTeam']['shortName'])) {
                            $this->fixtureCrests[TeamBadgeRegistry::normalize($match['awayTeam']['shortName'])] = $match['awayTeam']['crest'];
                        }
                    }
                }
            } catch (\Throwable) {
                // Ignore parse errors from invalid test fixture files
            }
        }

        return $this->fixtureCrests;
    }
}

