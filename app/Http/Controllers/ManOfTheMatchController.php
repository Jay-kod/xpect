<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Models\FootballMatch;
use App\Models\MotmEvaluation;
use App\Services\Motm\MotmEvaluator;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

class ManOfTheMatchController extends Controller
{
    public function __construct(
        protected readonly MotmEvaluator $motmEvaluator
    ) {
    }

    /**
     * Display the Man of the Match (MOTM) Hub & Player Performance Ratings.
     */
    public function index(Request $request): View
    {
        $compSlug = $request->query('competition');
        $competitions = Competition::where('is_active', true)->get();
        $selectedComp = $compSlug ? $competitions->firstWhere('slug', $compSlug) : null;
        $searchQuery = trim((string) $request->query('q', ''));

        // 1. MOTM Leaderboard: Top Players by awards count
        $playerLeaderboardQuery = MotmEvaluation::where('is_motm', true)
            ->select('player_name', 'team_id', DB::raw('COUNT(*) as motm_count'), DB::raw('AVG(rating) as avg_rating'), DB::raw('SUM(goals) as total_goals'))
            ->groupBy('player_name', 'team_id')
            ->orderByDesc('motm_count')
            ->orderByDesc('avg_rating')
            ->take(10);

        if ($selectedComp) {
            $playerLeaderboardQuery->whereHas('match', fn ($q) => $q->where('competition_id', $selectedComp->id));
        }
        $playerLeaderboard = $playerLeaderboardQuery->with('team')->get();

        // 2. MOTM Leaderboard: Top Teams
        $teamLeaderboardQuery = MotmEvaluation::where('is_motm', true)
            ->select('team_id', DB::raw('COUNT(*) as motm_count'))
            ->groupBy('team_id')
            ->orderByDesc('motm_count')
            ->take(8);

        if ($selectedComp) {
            $teamLeaderboardQuery->whereHas('match', fn ($q) => $q->where('competition_id', $selectedComp->id));
        }
        $teamLeaderboard = $teamLeaderboardQuery->with('team')->get();

        // 3. Finished Matches Query
        $finishedMatchesQuery = FootballMatch::where('status', 'finished')
            ->with([
                'homeTeam',
                'awayTeam',
                'competition',
                'motmEvaluations' => fn ($q) => $q->where('is_motm', true)->with('team'),
            ])
            ->orderBy('kickoff_at', 'desc');

        if ($selectedComp) {
            $finishedMatchesQuery->where('competition_id', $selectedComp->id);
        }

        if (!empty($searchQuery)) {
            $finishedMatchesQuery->where(function ($q) use ($searchQuery) {
                $q->whereHas('homeTeam', fn ($sq) => $sq->where('name', 'like', "%{$searchQuery}%")->orWhere('short_name', 'like', "%{$searchQuery}%"))
                  ->orWhereHas('awayTeam', fn ($sq) => $sq->where('name', 'like', "%{$searchQuery}%")->orWhere('short_name', 'like', "%{$searchQuery}%"))
                  ->orWhereHas('motmEvaluations', fn ($sq) => $sq->where('is_motm', true)->where('player_name', 'like', "%{$searchQuery}%"));
            });
        }

        $selectedDate = $request->filled('date') ? trim((string) $request->query('date')) : null;
        $dateFilterInfo = null;

        if ($selectedDate) {
            try {
                $parsedDate = CarbonImmutable::parse($selectedDate, 'UTC');
                $selectedDate = $parsedDate->format('Y-m-d');
                $dateStart = $parsedDate->startOfDay();
                $dateEnd = $parsedDate->endOfDay();

                $finishedMatchesQuery->whereBetween('kickoff_at', [$dateStart, $dateEnd]);

                $dateFilterInfo = [
                    'date_str' => $selectedDate,
                    'formatted' => $parsedDate->format('l, d M Y'),
                    'prev_date' => $parsedDate->subDay()->format('Y-m-d'),
                    'next_date' => $parsedDate->addDay()->format('Y-m-d'),
                ];
            } catch (Throwable) {
                $selectedDate = null;
                $dateFilterInfo = null;
            }
        }

        $finishedMatches = $finishedMatchesQuery->take(50)->get();

        // Auto-evaluate up to 10 unevaluated finished matches on the fly so users always see confirmed MOTMs
        $unevaluated = $finishedMatches->filter(fn ($m) => $m->motmEvaluations->isEmpty())->take(10);
        foreach ($unevaluated as $m) {
            $this->motmEvaluator->evaluateMatch($m);
            $m->load(['motmEvaluations' => fn ($q) => $q->where('is_motm', true)->with('team')]);
        }

        // 4. Resolve selected match for detail ratings spotlight
        $selectedMatchId = $request->query('match_id') ? (int) $request->query('match_id') : null;
        $selectedMatch = null;
        $matchEvaluations = collect();

        if ($selectedMatchId) {
            $selectedMatch = FootballMatch::with(['homeTeam', 'awayTeam', 'competition'])->find($selectedMatchId);
        } elseif ($finishedMatches->isNotEmpty()) {
            $selectedMatch = $finishedMatches->first();
        }

        if ($selectedMatch) {
            // Ensure selected match has evaluations
            $matchEvaluations = MotmEvaluation::where('match_id', $selectedMatch->id)
                ->with('team')
                ->orderByDesc('rating')
                ->get();

            if ($matchEvaluations->isEmpty()) {
                $matchEvaluations = $this->motmEvaluator->evaluateMatch($selectedMatch);
            }
        }

        // 5. Total MOTMs awarded count & single winner verification
        $totalAwardsCount = MotmEvaluation::where('is_motm', true)->count();
        $totalEvaluationsCount = MotmEvaluation::count();
        $uniqueMatchesWithMotm = MotmEvaluation::where('is_motm', true)->distinct('match_id')->count('match_id');

        return view('motm.index', [
            'competitions' => $competitions,
            'selectedComp' => $selectedComp,
            'searchQuery' => $searchQuery,
            'playerLeaderboard' => $playerLeaderboard,
            'teamLeaderboard' => $teamLeaderboard,
            'finishedMatches' => $finishedMatches,
            'selectedMatch' => $selectedMatch,
            'matchEvaluations' => $matchEvaluations,
            'totalAwardsCount' => $totalAwardsCount,
            'totalEvaluationsCount' => $totalEvaluationsCount,
            'uniqueMatchesWithMotm' => $uniqueMatchesWithMotm,
            'selectedDate' => $selectedDate,
            'dateFilterInfo' => $dateFilterInfo,
        ]);
    }

    /**
     * Evaluate single match and compute MOTM.
     */
    public function evaluate(FootballMatch $match): RedirectResponse
    {
        $this->motmEvaluator->evaluateMatch($match, force: true);

        return redirect()->route('motm', ['match_id' => $match->id])
            ->with('success', "Man of the Match & player ratings evaluated for {$match->homeTeam->short_name} vs {$match->awayTeam->short_name}!");
    }

    /**
     * Batch evaluate recent finished matches.
     */
    public function batchEvaluate(Request $request): RedirectResponse
    {
        $unevaluatedMatches = FootballMatch::where('status', 'finished')
            ->whereDoesntHave('motmEvaluations')
            ->orderBy('kickoff_at', 'desc')
            ->take(40)
            ->get();

        $count = 0;
        foreach ($unevaluatedMatches as $match) {
            $this->motmEvaluator->evaluateMatch($match, force: true);
            $count++;
        }

        return redirect()->route('motm')
            ->with('success', "Successfully evaluated confirmed Man of the Match awards for {$count} finished matches!");
    }
}
