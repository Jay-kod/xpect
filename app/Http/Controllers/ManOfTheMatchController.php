<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Models\FootballMatch;
use App\Models\MotmEvaluation;
use App\Services\Motm\VerifiedMotmResearcher;
use App\Services\Temporal\TemporalFilterService;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

class ManOfTheMatchController extends Controller
{
    public function __construct(
        protected readonly VerifiedMotmResearcher $motmResearcher
    ) {
    }

    /**
     * Display the Man of the Match (MOTM) Hub & Player Performance Ratings.
     */
    public function index(Request $request, TemporalFilterService $temporalService): View
    {
        $compSlug = $request->query('competition');
        $competitions = Competition::where('is_active', true)->get();
        $selectedComp = $compSlug ? $competitions->firstWhere('slug', $compSlug) : null;
        $searchQuery = trim((string) $request->query('q', ''));

        // 1. MOTM Leaderboard: Top Players by awards count
        $playerLeaderboardQuery = MotmEvaluation::where('is_motm', true)
            ->where('verification_status', 'verified')
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
            ->where('verification_status', 'verified')
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
                'motmEvaluations' => fn ($q) => $q->where('is_motm', true)->where('verification_status', 'verified')->with('team'),
            ])
            ->orderBy('kickoff_at', 'desc');

        if ($selectedComp) {
            $finishedMatchesQuery->where('competition_id', $selectedComp->id);
        }

        if (!empty($searchQuery)) {
            $finishedMatchesQuery->where(function ($q) use ($searchQuery) {
                $q->whereHas('homeTeam', fn ($sq) => $sq->where('name', 'like', "%{$searchQuery}%")->orWhere('short_name', 'like', "%{$searchQuery}%"))
                  ->orWhereHas('awayTeam', fn ($sq) => $sq->where('name', 'like', "%{$searchQuery}%")->orWhere('short_name', 'like', "%{$searchQuery}%"))
                  ->orWhereHas('motmEvaluations', fn ($sq) => $sq->where('is_motm', true)->where('verification_status', 'verified')->where('player_name', 'like', "%{$searchQuery}%"));
            });
        }

        $temporal = $temporalService->resolve($request, $selectedComp?->id);
        $selectedDate = $temporal['selectedDate'];
        $selectedTimeSlot = $temporal['selectedTimeSlot'];
        $selectedTime = $temporal['selectedTime'];
        $dateFilterInfo = $temporal['dateFilterInfo'];
        $availableMatchDates = $temporal['availableMatchDates'];

        if ($dateFilterInfo) {
            $finishedMatchesQuery->whereBetween('kickoff_at', [$temporal['dateStart'], $temporal['dateEnd']]);
        }

        $finishedMatches = $finishedMatchesQuery->take(50)->get();

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
                ->where('verification_status', 'verified')
                ->with('team')
                ->orderByDesc('rating')
                ->get();

            if ($matchEvaluations->isEmpty()) {
                $matchEvaluations = collect();
            }
        }

        // 5. Total MOTMs awarded count & single winner verification
        $totalAwardsCount = MotmEvaluation::where('is_motm', true)->where('verification_status', 'verified')->count();
        $totalEvaluationsCount = $totalAwardsCount;
        $uniqueMatchesWithMotm = MotmEvaluation::where('is_motm', true)->where('verification_status', 'verified')->distinct('match_id')->count('match_id');

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
            'selectedTimeSlot' => $selectedTimeSlot,
            'selectedTime' => $selectedTime,
            'dateFilterInfo' => $dateFilterInfo,
            'availableMatchDates' => $availableMatchDates,
        ]);
    }

    /**
     * Evaluate single match and compute MOTM.
     */
    public function evaluate(FootballMatch $match): RedirectResponse
    {
        try {
            $evaluation = $this->motmResearcher->research($match, force: true);
        } catch (Throwable $e) {
            return redirect()->route('motm', ['match_id' => $match->id])
                ->with('error', "Verified MOTM research failed: {$e->getMessage()}");
        }

        return redirect()->route('motm', ['match_id' => $match->id])
            ->with('success', $evaluation
                ? "Verified MOTM found for {$match->homeTeam->short_name} vs {$match->awayTeam->short_name}."
                : 'No source explicitly named a Man of the Match for this fixture.');
    }

    /**
     * Batch evaluate recent finished matches.
     */
    public function batchEvaluate(Request $request): RedirectResponse
    {
        $unevaluatedMatches = FootballMatch::where('status', 'finished')
            ->whereDoesntHave('motmEvaluations', fn ($query) => $query->where('verification_status', 'verified'))
            ->orderBy('kickoff_at', 'desc')
            ->take(40)
            ->get();

        $count = 0;
        foreach ($unevaluatedMatches as $match) {
            try {
                if ($this->motmResearcher->research($match)) {
                    $count++;
                }
            } catch (Throwable $e) {
                report($e);
            }
        }

        return redirect()->route('motm')
            ->with('success', "Successfully evaluated confirmed Man of the Match awards for {$count} finished matches!");
    }
}
