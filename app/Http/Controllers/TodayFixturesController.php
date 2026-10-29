<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\FootballMatch;
use App\Services\Predictions\PredictionService;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class TodayFixturesController extends Controller
{
    public function index(Request $request): View
    {
        $nowUtc = CarbonImmutable::now('UTC');
        $todayStr = $nowUtc->format('Y-m-d');
        $targetDateStr = (string) $request->get('date', $todayStr);

        try {
            $parsedDate = CarbonImmutable::parse($targetDateStr, 'UTC');
        } catch (Throwable) {
            $parsedDate = $nowUtc;
            $targetDateStr = $todayStr;
        }

        $startOfDay = $parsedDate->startOfDay();
        $endOfDay = $parsedDate->endOfDay();

        // Multi-league query with eager loading
        $matches = FootballMatch::with([
            'competition',
            'homeTeam',
            'awayTeam',
            'predictions' => fn($q) => $q->where('is_backtest', false)->latest(),
            'predictions.previews' => fn($q) => $q->latest(),
        ])
        ->whereBetween('kickoff_at', [$startOfDay, $endOfDay])
        ->orderBy('kickoff_at', 'asc')
        ->get();

        $groupedMatches = $matches->groupBy(fn($m) => $m->competition?->name ?? 'Other Competitions');

        // Next upcoming fixture for the empty state
        $nextUpcomingFixture = null;
        if ($matches->isEmpty()) {
            $nextUpcomingFixture = FootballMatch::with(['competition', 'homeTeam', 'awayTeam'])
                ->where('status', 'scheduled')
                ->where('kickoff_at', '>', $endOfDay)
                ->orderBy('kickoff_at', 'asc')
                ->first();
        }

        // Summary counts
        $totalMatches = $matches->count();
        $predictedMatches = $matches->filter(fn($m) => $m->predictions->isNotEmpty())->count();
        $finishedMatches = $matches->where('status', 'finished')->count();
        $scheduledMatches = $matches->where('status', 'scheduled')->count();
        $unpredictedMatches = $scheduledMatches - ($predictedMatches - ($matches->where('status', 'finished')->filter(fn($m) => $m->predictions->isNotEmpty())->count()));

        $summary = [
            'total' => $totalMatches,
            'predicted' => $predictedMatches,
            'finished' => $finishedMatches,
            'scheduled' => $scheduledMatches,
            'unpredicted' => max(0, $unpredictedMatches),
            'date_str' => $targetDateStr,
            'display_date' => $parsedDate->format('l, j F Y'),
            'is_today' => $targetDateStr === $todayStr,
            'is_yesterday' => $targetDateStr === $nowUtc->subDay()->format('Y-m-d'),
            'is_tomorrow' => $targetDateStr === $nowUtc->addDay()->format('Y-m-d'),
            'prev_date' => $parsedDate->subDay()->format('Y-m-d'),
            'next_date' => $parsedDate->addDay()->format('Y-m-d'),
            'today_date' => $todayStr,
        ];

        return view('today.index', [
            'matches' => $matches,
            'groupedMatches' => $groupedMatches,
            'nextUpcomingFixture' => $nextUpcomingFixture,
            'summary' => $summary,
        ]);
    }

    public function predictAll(Request $request, PredictionService $predictionService): RedirectResponse
    {
        $targetDateStr = (string) $request->get('date', CarbonImmutable::now('UTC')->format('Y-m-d'));

        try {
            $parsedDate = CarbonImmutable::parse($targetDateStr, 'UTC');
        } catch (Throwable) {
            $parsedDate = CarbonImmutable::now('UTC');
        }

        $startOfDay = $parsedDate->startOfDay();
        $endOfDay = $parsedDate->endOfDay();

        $matches = FootballMatch::whereBetween('kickoff_at', [$startOfDay, $endOfDay])
            ->where('status', 'scheduled')
            ->doesntHave('predictions')
            ->get();

        $computed = 0;
        $nowUtc = CarbonImmutable::now('UTC');

        foreach ($matches as $match) {
            try {
                // Strictly respect zero data leakage
                $asOf = $nowUtc->isBefore($match->kickoff_at) ? $nowUtc : CarbonImmutable::parse($match->kickoff_at);

                $predictionService->predict(
                    match: $match,
                    asOf: $asOf,
                    isBacktest: false
                );
                $computed++;
            } catch (Throwable) {
                // Continue batch execution if one match encounters an error
                continue;
            }
        }

        return redirect()->route('today', ['date' => $targetDateStr])
            ->with('success', "Computed {$computed} Poisson prediction(s) for {$parsedDate->format('D, d M')}.");
    }
}

