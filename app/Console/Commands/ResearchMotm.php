<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\FootballMatch;
use App\Services\Motm\VerifiedMotmResearcher;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Throwable;

class ResearchMotm extends Command
{
    protected $signature = 'motm:research {--date= : UTC date to research} {--limit= : Override daily limit} {--match= : Research one match ID} {--force}';
    protected $description = 'Research verified Man of the Match awards with Gemini Google Search grounding';

    public function handle(VerifiedMotmResearcher $researcher): int
    {
        $date = (string) ($this->option('date') ?: CarbonImmutable::now('UTC')->subDay()->toDateString());
        $limit = max(1, (int) ($this->option('limit') ?: config('football.motm.daily_limit', 12)));

        $query = FootballMatch::with(['homeTeam', 'awayTeam', 'competition'])
            ->where('status', 'finished')
            ->whereDate('kickoff_at', $date)
            ->orderByDesc('kickoff_at');

        if ($this->option('match')) {
            $query->whereKey((int) $this->option('match'));
        }

        $priorityTeams = collect(config('football.motm.priority_team_names', []))
            ->map(fn (string $name) => strtolower($name));
        $matches = $query->get()->sortByDesc(function (FootballMatch $match) use ($priorityTeams): int {
            return $priorityTeams->contains(fn (string $name) =>
                str_contains(strtolower($match->homeTeam?->name ?? ''), $name)
                || str_contains(strtolower($match->awayTeam?->name ?? ''), $name)
            ) ? 1 : 0;
        })->take($limit);

        if ($matches->isEmpty()) {
            $this->info("No finished matches found for {$date}.");
            return self::SUCCESS;
        }

        $verified = 0;
        foreach ($matches as $match) {
            try {
                $evaluation = $researcher->research($match, (bool) $this->option('force'));
                if ($evaluation) {
                    $verified++;
                    $this->info("Verified #{$match->id}: {$evaluation->player_name}");
                } else {
                    $this->warn("Unverified #{$match->id}: no explicit award found.");
                }
            } catch (Throwable $e) {
                $this->error("Failed #{$match->id}: {$e->getMessage()}");
            }
        }

        $this->info("Verified {$verified} of {$matches->count()} researched matches.");
        return self::SUCCESS;
    }
}
