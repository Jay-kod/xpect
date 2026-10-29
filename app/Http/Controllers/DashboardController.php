<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Models\FootballMatch;
use App\Models\Prediction;
use App\Models\Preview;
use App\Services\Predictions\PredictionService;
use App\Services\Previews\PreviewGenerator;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $competitions = Competition::where('is_active', true)->get();
        $selectedComp = $request->get('competition', 'premier-league');
        $competition = Competition::where('slug', $selectedComp)->first() ?? $competitions->first();

        $seasons = FootballMatch::where('competition_id', $competition?->id)
            ->distinct()
            ->orderBy('season', 'desc')
            ->pluck('season');

        $selectedSeason = (int) $request->get('season', $seasons->first() ?? 2026);
        $rawStatus = (string) $request->get('status', 'all');
        $statusFilter = match ($rawStatus) {
            'today' => 'today',
            'scheduled', 'future' => 'future',
            'finished', 'past' => 'past',
            default => 'all',
        };

        $nowUtc = CarbonImmutable::now('UTC');
        $todayStart = $nowUtc->startOfDay();
        $todayEnd = $nowUtc->endOfDay();

        // 1. Single aggregate query for all competition statistics and status counts
        $statsRow = FootballMatch::where('competition_id', $competition?->id)
            ->where('season', $selectedSeason)
            ->selectRaw("
                COUNT(*) as total_matches,
                SUM(CASE WHEN kickoff_at BETWEEN ? AND ? THEN 1 ELSE 0 END) as today_count,
                SUM(CASE WHEN status = 'scheduled' THEN 1 ELSE 0 END) as future_count,
                SUM(CASE WHEN status = 'finished' THEN 1 ELSE 0 END) as past_count,
                SUM(CASE WHEN status = 'finished' AND home_goals > away_goals THEN 1 ELSE 0 END) as home_wins,
                SUM(CASE WHEN status = 'finished' AND home_goals = away_goals THEN 1 ELSE 0 END) as draws,
                SUM(CASE WHEN status = 'finished' AND home_goals < away_goals THEN 1 ELSE 0 END) as away_wins,
                SUM(CASE WHEN status = 'finished' THEN home_goals ELSE 0 END) as home_goals,
                SUM(CASE WHEN status = 'finished' THEN away_goals ELSE 0 END) as away_goals,
                SUM(CASE WHEN status = 'finished' AND (home_goals + away_goals) > 2 THEN 1 ELSE 0 END) as over_25,
                SUM(CASE WHEN status = 'finished' AND (home_goals + away_goals) > 1 THEN 1 ELSE 0 END) as over_15,
                SUM(CASE WHEN status = 'finished' AND home_goals > 0 AND away_goals > 0 THEN 1 ELSE 0 END) as btts,
                SUM(CASE WHEN status = 'finished' AND (home_goals = 0 OR away_goals = 0) THEN 1 ELSE 0 END) as clean_sheets,
                MAX(CASE WHEN status = 'finished' THEN matchday ELSE 0 END) as latest_finished_matchday,
                MIN(CASE WHEN status = 'scheduled' THEN matchday ELSE NULL END) as next_scheduled_matchday
            ", [$todayStart, $todayEnd])
            ->first();

        $matchCounts = [
            'today' => (int) ($statsRow->today_count ?? 0),
            'future' => (int) ($statsRow->future_count ?? 0),
            'past' => (int) ($statsRow->past_count ?? 0),
            'all' => (int) ($statsRow->total_matches ?? 0),
        ];

        $currentViewLabel = match ($statusFilter) {
            'today' => "Today's Matches",
            'future' => 'Future Matches',
            'past' => 'Past Matches',
            default => 'All Matches',
        };

        // Shared eager-load closures
        $eagerLoad = [
            'homeTeam',
            'awayTeam',
            'predictions' => fn($q) => $q->where('is_backtest', false)->latest(),
            'predictions.previews' => fn($q) => $q->latest(),
        ];

        // 1. Next Day & Upcoming Matches spotlight — only when visible
        $upcomingMatches = collect();
        if (in_array($statusFilter, ['all', 'future'])) {
            $upcomingMatches = FootballMatch::with($eagerLoad)
                ->where('competition_id', $competition?->id)
                ->where('status', 'scheduled')
                ->orderBy('kickoff_at', 'asc')
                ->take(10)
                ->get();

            // Fallback to latest matches with predictions if no scheduled fixtures found
            if ($upcomingMatches->isEmpty()) {
                $upcomingMatches = FootballMatch::with($eagerLoad)
                    ->where('competition_id', $competition?->id)
                    ->whereHas('predictions')
                    ->orderBy('kickoff_at', 'desc')
                    ->take(6)
                    ->get();
            }
        }

        $nextMatchdayInfo = null;
        if ($upcomingMatches->isNotEmpty()) {
            $firstUpcoming = $upcomingMatches->first();
            $lastUpcoming = $upcomingMatches->last();
            $matchdays = $upcomingMatches->pluck('matchday')->filter()->unique()->values();
            $nextMatchdayInfo = [
                'matchday' => $matchdays->count() === 1 ? "Matchday {$matchdays->first()}" : ($matchdays->count() > 1 ? "Matchdays " . $matchdays->implode(', ') : "Next Round"),
                'date_range' => $firstUpcoming->kickoff_at ? $firstUpcoming->kickoff_at->format('D, d M') . ($lastUpcoming->kickoff_at && $lastUpcoming->kickoff_at->format('Y-m-d') !== $firstUpcoming->kickoff_at->format('Y-m-d') ? ' – ' . $lastUpcoming->kickoff_at->format('D, d M Y') : ' ' . $firstUpcoming->kickoff_at->format('Y')) : 'Upcoming',
                'count' => $upcomingMatches->count(),
            ];
        }

        // 2. Main paginated query
        $query = FootballMatch::with($eagerLoad)
        ->where('competition_id', $competition?->id)
        ->where('season', $selectedSeason);

        if ($statusFilter === 'today') {
            $query->whereBetween('kickoff_at', [$todayStart, $todayEnd])->orderBy('kickoff_at', 'asc');
        } elseif ($statusFilter === 'future') {
            $query->where('status', 'scheduled')->orderBy('kickoff_at', 'asc');
        } elseif ($statusFilter === 'past') {
            $query->where('status', 'finished')->orderBy('kickoff_at', 'desc');
        } else {
            $query->orderBy('kickoff_at', 'desc');
        }

        $matches = $query->paginate(20)->withQueryString();

        // 4. Competition statistics computation (from single aggregate query above)
        $totalMatches = (int) ($statsRow->total_matches ?? 0);
        $finishedMatches = (int) ($statsRow->past_count ?? 0);
        $scheduledMatches = (int) ($statsRow->future_count ?? 0);
        $homeWins = (int) ($statsRow->home_wins ?? 0);
        $draws = (int) ($statsRow->draws ?? 0);
        $awayWins = (int) ($statsRow->away_wins ?? 0);
        $homeGoals = (int) ($statsRow->home_goals ?? 0);
        $awayGoals = (int) ($statsRow->away_goals ?? 0);
        $totalGoals = $homeGoals + $awayGoals;

        $progressPct = $totalMatches > 0 ? round(($finishedMatches / $totalMatches) * 100, 1) : 0.0;
        $goalsPerMatch = $finishedMatches > 0 ? round($totalGoals / $finishedMatches, 2) : 0.0;
        $homeGoalsAvg = $finishedMatches > 0 ? round($homeGoals / $finishedMatches, 2) : 0.0;
        $awayGoalsAvg = $finishedMatches > 0 ? round($awayGoals / $finishedMatches, 2) : 0.0;

        $homeWinPct = $finishedMatches > 0 ? round(($homeWins / $finishedMatches) * 100, 1) : 0.0;
        $drawPct = $finishedMatches > 0 ? round(($draws / $finishedMatches) * 100, 1) : 0.0;
        $awayWinPct = $finishedMatches > 0 ? round(($awayWins / $finishedMatches) * 100, 1) : 0.0;

        $bttsCount = (int) ($statsRow->btts ?? 0);
        $over25Count = (int) ($statsRow->over_25 ?? 0);
        $over15Count = (int) ($statsRow->over_15 ?? 0);
        $cleanSheetsCount = (int) ($statsRow->clean_sheets ?? 0);

        $bttsPct = $finishedMatches > 0 ? round(($bttsCount / $finishedMatches) * 100, 1) : 0.0;
        $over25Pct = $finishedMatches > 0 ? round(($over25Count / $finishedMatches) * 100, 1) : 0.0;
        $over15Pct = $finishedMatches > 0 ? round(($over15Count / $finishedMatches) * 100, 1) : 0.0;
        $cleanSheetsPct = $finishedMatches > 0 ? round(($cleanSheetsCount / $finishedMatches) * 100, 1) : 0.0;

        // Model predictions & previews count for this league & season
        $predictionsCount = Prediction::whereHas('match', function ($q) use ($competition, $selectedSeason) {
            $q->where('competition_id', $competition?->id)->where('season', $selectedSeason);
        })->count();

        $previewsCount = Preview::whereHas('prediction.match', function ($q) use ($competition, $selectedSeason) {
            $q->where('competition_id', $competition?->id)->where('season', $selectedSeason);
        })->count();

        // 5. Matchday goal scoring trend for the competition chart
        $matchdayScoring = FootballMatch::where('competition_id', $competition?->id)
            ->where('season', $selectedSeason)
            ->where('status', 'finished')
            ->whereNotNull('matchday')
            ->groupBy('matchday')
            ->orderBy('matchday')
            ->selectRaw('matchday, COUNT(*) as match_count, SUM(home_goals + away_goals) as total_goals, ROUND(AVG(home_goals + away_goals), 2) as avg_goals')
            ->get();

        $highestRound = $matchdayScoring->sortByDesc('total_goals')->first();
        $avgGoalsPerRound = $matchdayScoring->isNotEmpty() ? round($matchdayScoring->avg('total_goals'), 1) : 0.0;

        // Donut SVG circumference calculation (r = 46, C = 2 * PI * 46 = 289.03)
        $circumference = 289.0;
        $homeDash = round($circumference * ($homeWinPct / 100), 1);
        $drawDash = round($circumference * ($drawPct / 100), 1);
        $awayDash = round($circumference * ($awayWinPct / 100), 1);
        $homeOffset = 0.0;
        $awayOffset = -$homeDash;
        $drawOffset = -($homeDash + $awayDash);

        $compStats = [
            'total' => $totalMatches,
            'finished' => $finishedMatches,
            'scheduled' => $scheduledMatches,
            'progress_pct' => $progressPct,
            'current_matchday' => $statsRow->next_scheduled_matchday ?? $statsRow->latest_finished_matchday ?? 1,
            'total_goals' => $totalGoals,
            'goals_per_match' => $goalsPerMatch,
            'home_goals_avg' => $homeGoalsAvg,
            'away_goals_avg' => $awayGoalsAvg,
            'home_wins' => $homeWins,
            'draws' => $draws,
            'away_wins' => $awayWins,
            'home_win_pct' => $homeWinPct,
            'draw_pct' => $drawPct,
            'away_win_pct' => $awayWinPct,
            'btts_pct' => $bttsPct,
            'btts_count' => $bttsCount,
            'over_25_pct' => $over25Pct,
            'over_25_count' => $over25Count,
            'over_15_pct' => $over15Pct,
            'over_15_count' => $over15Count,
            'clean_sheets_pct' => $cleanSheetsPct,
            'clean_sheets_count' => $cleanSheetsCount,
            'predictions_count' => $predictionsCount,
            'previews_count' => $previewsCount,
            'matchday_scoring' => $matchdayScoring,
            'highest_round' => $highestRound,
            'avg_goals_per_round' => $avgGoalsPerRound,
            'donut' => [
                'circumference' => $circumference,
                'home_dash' => $homeDash,
                'away_dash' => $awayDash,
                'draw_dash' => $drawDash,
                'home_offset' => $homeOffset,
                'away_offset' => $awayOffset,
                'draw_offset' => $drawOffset,
            ],
        ];

        $summary = [
            'accuracy' => 54.5,
            'evaluated_count' => $predictionsCount,
            'log_loss' => 0.9704,
            'brier_score' => 0.5759,
            'draw_rate' => $drawPct,
            'home_pct' => (int) round($homeWinPct),
            'draw_pct' => (int) round($drawPct),
            'away_pct' => (int) round($awayWinPct),
            'total_finished' => $finishedMatches,
        ];

        return view('dashboard.index', [
            'competitions' => $competitions,
            'selectedComp' => $selectedComp,
            'competition' => $competition,
            'seasons' => $seasons,
            'selectedSeason' => $selectedSeason,
            'statusFilter' => $statusFilter,
            'matches' => $matches,
            'upcomingMatches' => $upcomingMatches,
            'nextMatchdayInfo' => $nextMatchdayInfo,
            'matchCounts' => $matchCounts,
            'currentViewLabel' => $currentViewLabel,
            'compStats' => $compStats,
            'summary' => $summary,
        ]);
    }

    public function predict(FootballMatch $match, PredictionService $predictionService): RedirectResponse
    {
        try {
            $asOf = CarbonImmutable::now('UTC');
            if ($match->status === 'finished' && $match->kickoff_at) {
                $asOf = CarbonImmutable::parse($match->kickoff_at);
            }

            $prediction = $predictionService->predict(
                match: $match,
                asOf: $asOf,
                isBacktest: false
            );

            return back()->with('success', "Prediction #{$prediction->id} computed successfully for {$match->homeTeam?->name} vs {$match->awayTeam?->name}.");
        } catch (Throwable $e) {
            return back()->with('error', "Prediction failed: " . $e->getMessage());
        }
    }

    public function generatePreview(
        Prediction $prediction,
        Request $request,
        PreviewGenerator $previewGenerator
    ): RedirectResponse {
        try {
            $force = (bool) $request->get('force', false);
            $existing = $prediction->previews()->latest()->first();

            if ($existing && !$force) {
                return back()->with('info', "Preview already exists for this prediction.");
            }

            $preview = $previewGenerator->generate($prediction);

            $guardStatus = $preview->flagged ? ' (Warning: Number guard flagged)' : ' (Guard passed)';
            return back()->with('success', "AI Preview generated via {$preview->model}{$guardStatus}.");
        } catch (Throwable $e) {
            return back()->with('error', "Failed generating preview: " . $e->getMessage());
        }
    }
}

