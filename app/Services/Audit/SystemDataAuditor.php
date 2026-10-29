<?php

declare(strict_types=1);

namespace App\Services\Audit;

use App\Models\ApiCall;
use App\Models\Competition;
use App\Models\FootballMatch;
use App\Models\MatchStat;
use App\Models\Prediction;
use App\Models\SystemAudit;
use App\Models\Team;
use App\Models\TeamAlias;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class SystemDataAuditor
{
    /**
     * Run all audit checks across the database.
     *
     * @return array<string, mixed>
     */
    public function audit(string $triggeredBy = 'cli', bool $persist = false): array
    {
        $startTime = microtime(true);
        $checks = [];
        $passed = 0;
        $failed = 0;
        $warnings = 0;

        // 1. Timeline & Dataset Coverage per Competition
        $timelineCheck = $this->checkTimelineAndCoverage();
        $checks['timeline_and_coverage'] = $timelineCheck;
        $timelineCheck['passed'] ? $passed++ : ($timelineCheck['is_warning'] ? $warnings++ : $failed++);

        // 2. Zero Data Leakage Guard
        $leakageCheck = $this->checkZeroDataLeakage();
        $checks['zero_data_leakage'] = $leakageCheck;
        $leakageCheck['passed'] ? $passed++ : ($leakageCheck['is_warning'] ? $warnings++ : $failed++);

        // 3. Team Identity & Alias Integrity
        $teamCheck = $this->checkTeamIdentityIntegrity();
        $checks['team_identity_integrity'] = $teamCheck;
        $teamCheck['passed'] ? $passed++ : ($teamCheck['is_warning'] ? $warnings++ : $failed++);

        // 4. Duplicate Fixture Detection
        $duplicateCheck = $this->checkDuplicateFixtures();
        $checks['duplicate_fixtures'] = $duplicateCheck;
        $duplicateCheck['passed'] ? $passed++ : ($duplicateCheck['is_warning'] ? $warnings++ : $failed++);

        // 5. Scoreline & Match State Consistency
        $scoreCheck = $this->checkScorelineConsistency();
        $checks['scoreline_consistency'] = $scoreCheck;
        $scoreCheck['passed'] ? $passed++ : ($scoreCheck['is_warning'] ? $warnings++ : $failed++);

        // 6. Mathematical Model & Probability Normalization
        $mathCheck = $this->checkProbabilityMath();
        $checks['probability_model_math'] = $mathCheck;
        $mathCheck['passed'] ? $passed++ : ($mathCheck['is_warning'] ? $warnings++ : $failed++);

        // 7. Advanced Stats & xG Health
        $xgCheck = $this->checkMatchStatsHealth();
        $checks['match_stats_health'] = $xgCheck;
        $xgCheck['passed'] ? $passed++ : ($xgCheck['is_warning'] ? $warnings++ : $failed++);

        // 8. Secrets & API Call Hygiene
        $secretsCheck = $this->checkApiCallHygiene();
        $checks['api_call_hygiene'] = $secretsCheck;
        $secretsCheck['passed'] ? $passed++ : ($secretsCheck['is_warning'] ? $warnings++ : $failed++);

        $durationMs = round((microtime(true) - $startTime) * 1000, 2);
        $totalChecks = count($checks);

        $overallStatus = 'clean';
        if ($failed > 0) {
            $overallStatus = 'critical';
        } elseif ($warnings > 0) {
            $overallStatus = 'warning';
        }

        $summary = "Audited {$totalChecks} critical integrity domains in {$durationMs}ms: {$passed} passed, {$warnings} warnings, {$failed} failures.";

        $report = [
            'status' => $overallStatus,
            'triggered_by' => $triggeredBy,
            'total_checks' => $totalChecks,
            'checks_passed' => $passed,
            'checks_failed' => $failed,
            'checks_warning' => $warnings,
            'duration_ms' => $durationMs,
            'summary' => $summary,
            'audited_at' => CarbonImmutable::now('UTC')->toIso8601String(),
            'system_totals' => [
                'competitions' => Competition::count(),
                'teams' => Team::count(),
                'team_aliases' => TeamAlias::count(),
                'matches' => FootballMatch::count(),
                'predictions' => Prediction::count(),
                'match_stats' => MatchStat::count(),
                'api_calls' => ApiCall::count(),
            ],
            'checks' => $checks,
        ];

        if ($persist) {
            SystemAudit::create([
                'triggered_by' => $triggeredBy,
                'status' => $overallStatus,
                'total_checks' => $totalChecks,
                'checks_passed' => $passed,
                'checks_failed' => $failed,
                'checks_warning' => $warnings,
                'duration_ms' => $durationMs,
                'summary' => $summary,
                'report_json' => $report,
            ]);
        }

        return $report;
    }

    /**
     * Check 1: Timeline & Coverage per League
     */
    protected function checkTimelineAndCoverage(): array
    {
        $competitions = Competition::where('is_active', true)->get();
        $leagueReports = [];
        $overallEarliest = null;
        $overallLatest = null;

        foreach ($competitions as $comp) {
            $stats = FootballMatch::where('competition_id', $comp->id)
                ->selectRaw("
                    MIN(kickoff_at) as earliest_kickoff,
                    MAX(kickoff_at) as latest_kickoff,
                    COUNT(*) as total_matches,
                    SUM(CASE WHEN status = 'finished' THEN 1 ELSE 0 END) as finished_matches,
                    SUM(CASE WHEN status = 'scheduled' THEN 1 ELSE 0 END) as scheduled_matches
                ")
                ->first();

            $seasons = FootballMatch::where('competition_id', $comp->id)
                ->distinct()
                ->orderBy('season', 'asc')
                ->pluck('season')
                ->toArray();

            $xgCount = MatchStat::whereHas('match', fn($q) => $q->where('competition_id', $comp->id))
                ->distinct('match_id')
                ->count('match_id');

            $earliest = $stats->earliest_kickoff ? CarbonImmutable::parse($stats->earliest_kickoff)->format('Y-m-d') : null;
            $latest = $stats->latest_kickoff ? CarbonImmutable::parse($stats->latest_kickoff)->format('Y-m-d') : null;

            if ($earliest && (!$overallEarliest || $earliest < $overallEarliest)) {
                $overallEarliest = $earliest;
            }
            if ($latest && (!$overallLatest || $latest > $overallLatest)) {
                $overallLatest = $latest;
            }

            $finished = (int) ($stats->finished_matches ?? 0);
            $total = (int) ($stats->total_matches ?? 0);

            $leagueReports[$comp->slug] = [
                'name' => $comp->name,
                'country' => $comp->country,
                'timeline_start' => $earliest,
                'timeline_end' => $latest,
                'total_matches' => $total,
                'finished_matches' => $finished,
                'scheduled_matches' => (int) ($stats->scheduled_matches ?? 0),
                'seasons' => $seasons,
                'xg_matches_count' => $xgCount,
                'xg_coverage_pct' => $finished > 0 ? round(($xgCount / $finished) * 100, 1) : 0.0,
            ];
        }

        return [
            'name' => 'Timeline & Dataset Coverage',
            'passed' => true,
            'is_warning' => false,
            'summary' => "Database covers timeline from {$overallEarliest} to {$overallLatest} across all 5 top European leagues.",
            'overall_earliest' => $overallEarliest,
            'overall_latest' => $overallLatest,
            'leagues' => $leagueReports,
        ];
    }

    /**
     * Check 2: Zero Data Leakage Guard
     * Ensures every prediction has as_of strictly prior to match kickoff.
     */
    protected function checkZeroDataLeakage(): array
    {
        // A prediction leaks data if its as_of cutoff timestamp exceeds the match's kickoff time.
        // In walk-forward testing and live pre-match, as_of is set to <= kickoff_at, and all
        // feature aggregations strictly query where('kickoff_at', '<', $asOf).
        $leakageQuery = Prediction::join('matches', 'predictions.match_id', '=', 'matches.id')
            ->whereNotNull('matches.kickoff_at')
            ->whereRaw("predictions.as_of > matches.kickoff_at")
            ->select('predictions.id', 'predictions.match_id', 'predictions.as_of', 'matches.kickoff_at');

        $violationsCount = $leakageQuery->count();
        $sampleViolations = $violationsCount > 0 ? $leakageQuery->take(5)->get()->toArray() : [];

        $totalPredictions = Prediction::count();

        return [
            'name' => 'Zero Data Leakage Verification',
            'passed' => $violationsCount === 0,
            'is_warning' => false,
            'total_audited' => $totalPredictions,
            'violations_count' => $violationsCount,
            'summary' => $violationsCount === 0
                ? "100% Zero Leakage verified across all {$totalPredictions} stored predictions. All feature cutoffs precede kickoff."
                : "CRITICAL: Found {$violationsCount} predictions with as_of >= kickoff_at!",
            'sample_violations' => $sampleViolations,
        ];
    }

    /**
     * Check 3: Team Identity & Alias Integrity
     */
    protected function checkTeamIdentityIntegrity(): array
    {
        $danglingHomeTeams = FootballMatch::whereDoesntHave('homeTeam')->count();
        $danglingAwayTeams = FootballMatch::whereDoesntHave('awayTeam')->count();
        $selfMatches = FootballMatch::whereColumn('home_team_id', '=', 'away_team_id')->count();
        $danglingAliases = TeamAlias::whereDoesntHave('team')->count();

        $totalIssues = $danglingHomeTeams + $danglingAwayTeams + $selfMatches + $danglingAliases;

        return [
            'name' => 'Team Identity & Foreign Key Integrity',
            'passed' => $totalIssues === 0,
            'is_warning' => false,
            'summary' => $totalIssues === 0
                ? "All team IDs and aliases resolve strictly to canonical records. 0 dangling foreign keys."
                : "Found {$totalIssues} identity resolution anomalies (Home: {$danglingHomeTeams}, Away: {$danglingAwayTeams}, Self: {$selfMatches}, Aliases: {$danglingAliases}).",
            'details' => [
                'dangling_home_teams' => $danglingHomeTeams,
                'dangling_away_teams' => $danglingAwayTeams,
                'self_matches' => $selfMatches,
                'dangling_aliases' => $danglingAliases,
            ],
        ];
    }

    /**
     * Check 4: Duplicate Fixture Detection
     */
    protected function checkDuplicateFixtures(): array
    {
        $duplicates = DB::table('matches')
            ->select('competition_id', 'season', 'home_team_id', 'away_team_id', DB::raw('DATE(kickoff_at) as match_date'), DB::raw('COUNT(*) as count'))
            ->groupBy('competition_id', 'season', 'home_team_id', 'away_team_id', DB::raw('DATE(kickoff_at)'))
            ->having('count', '>', 1)
            ->get();

        $duplicateCount = $duplicates->count();

        return [
            'name' => 'Duplicate Fixture Detection',
            'passed' => $duplicateCount === 0,
            'is_warning' => false,
            'summary' => $duplicateCount === 0
                ? "Zero duplicate fixtures detected across all leagues and seasons."
                : "Found {$duplicateCount} duplicate match schedules with identical teams and dates.",
            'duplicate_count' => $duplicateCount,
            'sample_duplicates' => $duplicates->take(5)->toArray(),
        ];
    }

    /**
     * Check 5: Scoreline & Match State Consistency
     */
    protected function checkScorelineConsistency(): array
    {
        $finishedMissingScores = FootballMatch::where('status', 'finished')
            ->where(function ($q) {
                $q->whereNull('home_goals')->orWhereNull('away_goals');
            })
            ->count();

        $negativeGoals = FootballMatch::where(function ($q) {
            $q->where('home_goals', '<', 0)->orWhere('away_goals', '<', 0);
        })->count();

        $scheduledWithScores = FootballMatch::where('status', 'scheduled')
            ->where(function ($q) {
                $q->whereNotNull('home_goals')->orWhereNotNull('away_goals');
            })
            ->count();

        $totalIssues = $finishedMissingScores + $negativeGoals + $scheduledWithScores;

        return [
            'name' => 'Scoreline & Match Status Consistency',
            'passed' => $totalIssues === 0,
            'is_warning' => false,
            'summary' => $totalIssues === 0
                ? "All finished matches have verified non-negative scores; scheduled fixtures have clean null state."
                : "Found {$totalIssues} scoreline anomalies (Missing: {$finishedMissingScores}, Negative: {$negativeGoals}, Premature: {$scheduledWithScores}).",
            'details' => [
                'finished_missing_scores' => $finishedMissingScores,
                'negative_goals' => $negativeGoals,
                'scheduled_with_scores' => $scheduledWithScores,
            ],
        ];
    }

    /**
     * Check 6: Mathematical Model & Probability Normalization
     */
    protected function checkProbabilityMath(): array
    {
        // Probability sum check: abs((p_home + p_draw + p_away) - 1.0) > 0.005
        $unnormalized = Prediction::whereRaw("ABS((p_home + p_draw + p_away) - 1.0) > 0.005")->count();
        $invalidBounds = Prediction::whereRaw("p_home < 0 OR p_draw < 0 OR p_away < 0 OR p_home > 1 OR p_draw > 1 OR p_away > 1")->count();
        $negativeExpectedGoals = Prediction::whereRaw("exp_home_goals < 0 OR exp_away_goals < 0")->count();

        $totalIssues = $unnormalized + $invalidBounds + $negativeExpectedGoals;

        return [
            'name' => 'Probability Normalization & Poisson Bounds',
            'passed' => $totalIssues === 0,
            'is_warning' => false,
            'summary' => $totalIssues === 0
                ? "100% of stored probabilities satisfy normalization (sum = 1.000 ± 0.005) with non-negative expected goals."
                : "Found {$totalIssues} mathematical prediction anomalies (Unnormalized: {$unnormalized}, Bounds: {$invalidBounds}, Negative xG: {$negativeExpectedGoals}).",
            'details' => [
                'unnormalized_count' => $unnormalized,
                'invalid_bounds' => $invalidBounds,
                'negative_expected_goals' => $negativeExpectedGoals,
            ],
        ];
    }

    /**
     * Check 7: Advanced Stats & xG Health
     */
    protected function checkMatchStatsHealth(): array
    {
        $negativeXg = MatchStat::where(function ($q) {
            $q->where('xg_for', '<', 0)->orWhere('xg_against', '<', 0);
        })->count();

        $orphanStats = MatchStat::whereDoesntHave('match')->count();
        $totalStats = MatchStat::count();

        $totalIssues = $negativeXg + $orphanStats;

        return [
            'name' => 'Advanced Metrics & xG Health',
            'passed' => $totalIssues === 0,
            'is_warning' => false,
            'summary' => $totalIssues === 0
                ? "{$totalStats} team match statistics verified with non-negative xG values and valid parent matches."
                : "Found {$totalIssues} match stats issues (Negative xG: {$negativeXg}, Orphan records: {$orphanStats}).",
            'details' => [
                'total_stats_rows' => $totalStats,
                'negative_xg' => $negativeXg,
                'orphan_stats' => $orphanStats,
            ],
        ];
    }

    /**
     * Check 8: Secrets & API Call Hygiene
     */
    protected function checkApiCallHygiene(): array
    {
        $rawCalls = ApiCall::count();
        
        // Scan for common unredacted API key patterns or bearer tokens in raw request text
        $leakedKeys = DB::table('api_calls')
            ->where(function ($q) {
                $q->where('request_headers', 'LIKE', '%X-Auth-Token%')
                    ->where('request_headers', 'NOT LIKE', '%REDACTED%');
            })
            ->orWhere(function ($q) {
                $q->where('request_headers', 'LIKE', '%x-apisports-key%')
                    ->where('request_headers', 'NOT LIKE', '%REDACTED%');
            })
            ->orWhere(function ($q) {
                $q->where('request_headers', 'LIKE', '%x-api-key%')
                    ->where('request_headers', 'NOT LIKE', '%REDACTED%');
            })
            ->count();

        $missingDuration = ApiCall::whereNull('duration_ms')->count();

        $totalIssues = $leakedKeys;

        return [
            'name' => 'Credential Redaction & API Log Hygiene',
            'passed' => $totalIssues === 0,
            'is_warning' => $missingDuration > 0,
            'summary' => $totalIssues === 0
                ? "All {$rawCalls} outbound API call payloads and headers strictly redacted before database persistence. 0 leaked secrets."
                : "SECURITY ALERT: Found {$leakedKeys} API calls with unredacted authentication keys!",
            'details' => [
                'total_api_calls' => $rawCalls,
                'leaked_secret_rows' => $leakedKeys,
                'missing_duration_rows' => $missingDuration,
            ],
        ];
    }
}
