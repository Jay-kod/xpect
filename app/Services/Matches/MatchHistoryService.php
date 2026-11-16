<?php

declare(strict_types=1);

namespace App\Services\Matches;

use App\Models\FootballMatch;
use App\Models\MotmEvaluation;
use App\Services\Motm\MotmEvaluator;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class MatchHistoryService
{
    /** @var array<string, array> */
    private static array $h2hCache = [];

    /** @var array<int, array> */
    private static array $perfCache = [];

    public function __construct(
        protected readonly MotmEvaluator $motmEvaluator
    ) {
    }

    /**
     * Clear in-memory caches (useful in long-running processes/tests).
     */
    public static function clearCache(): void
    {
        self::$h2hCache = [];
        self::$perfCache = [];
    }

    /**
     * Retrieve past Head-to-Head encounters between the two clubs.
     * ZERO DATA LEAKAGE: strictly limits to kickoff_at < match.kickoff_at.
     */
    public function getHeadToHead(FootballMatch $match, int $limit = 6): array
    {
        if (!$match->home_team_id || !$match->away_team_id) {
            return [
                'matches' => collect(),
                'total_played' => 0,
                'home_wins' => 0,
                'draws' => 0,
                'away_wins' => 0,
                'home_win_pct' => 0.0,
                'draw_pct' => 0.0,
                'away_win_pct' => 0.0,
                'home_goals_total' => 0,
                'away_goals_total' => 0,
                'avg_goals' => 0.0,
                'btts_count' => 0,
                'btts_pct' => 0.0,
                'over_25_count' => 0,
                'over_25_pct' => 0.0,
                'clean_sheets_home' => 0,
                'clean_sheets_away' => 0,
                'most_frequent_score' => null,
            ];
        }

        $cutoff = $match->kickoff_at
            ? CarbonImmutable::parse($match->kickoff_at)
            : CarbonImmutable::now('UTC');

        $cutoffStr = $cutoff->toIso8601String();
        $cacheKey = "{$match->home_team_id}_{$match->away_team_id}_{$cutoffStr}_{$limit}";
        if (isset(self::$h2hCache[$cacheKey])) {
            return self::$h2hCache[$cacheKey];
        }

        $query = FootballMatch::with([
            'homeTeam',
            'awayTeam',
            'competition',
            'motmEvaluations' => fn ($q) => $q->where('is_motm', true)->with('team'),
        ])
        ->where('status', 'finished')
        ->whereNotNull('home_goals')
        ->whereNotNull('away_goals')
        ->where('kickoff_at', '<', $cutoff)
        ->where(function ($q) use ($match) {
            $q->where(function ($sq) use ($match) {
                $sq->where('home_team_id', $match->home_team_id)
                   ->where('away_team_id', $match->away_team_id);
            })->orWhere(function ($sq) use ($match) {
                $sq->where('home_team_id', $match->away_team_id)
                   ->where('away_team_id', $match->home_team_id);
            });
        })
        ->orderBy('kickoff_at', 'desc');

        $allH2H = $query->get();
        $totalPlayed = $allH2H->count();

        $homeClubId = $match->home_team_id;
        $awayClubId = $match->away_team_id;

        $homeWins = 0;
        $awayWins = 0;
        $draws = 0;
        $homeGoalsTotal = 0;
        $awayGoalsTotal = 0;
        $bttsCount = 0;
        $over25Count = 0;
        $cleanSheetsHome = 0;
        $cleanSheetsAway = 0;
        $scoreCounts = [];

        foreach ($allH2H as $past) {
            $hGoals = (int) $past->home_goals;
            $aGoals = (int) $past->away_goals;
            $totGoals = $hGoals + $aGoals;

            // Determine goals for current match's home team vs away team
            $currentHomeGoalsInPast = $past->home_team_id === $homeClubId ? $hGoals : $aGoals;
            $currentAwayGoalsInPast = $past->away_team_id === $awayClubId ? $aGoals : $hGoals;

            $homeGoalsTotal += $currentHomeGoalsInPast;
            $awayGoalsTotal += $currentAwayGoalsInPast;

            if ($currentHomeGoalsInPast > $currentAwayGoalsInPast) {
                $homeWins++;
            } elseif ($currentAwayGoalsInPast > $currentHomeGoalsInPast) {
                $awayWins++;
            } else {
                $draws++;
            }

            if ($hGoals > 0 && $aGoals > 0) {
                $bttsCount++;
            }

            if ($totGoals > 2) {
                $over25Count++;
            }

            if ($currentAwayGoalsInPast === 0) {
                $cleanSheetsHome++;
            }
            if ($currentHomeGoalsInPast === 0) {
                $cleanSheetsAway++;
            }

            $scoreKey = "{$hGoals}-{$aGoals}";
            $scoreCounts[$scoreKey] = ($scoreCounts[$scoreKey] ?? 0) + 1;
        }

        arsort($scoreCounts);
        $mostFrequentScore = !empty($scoreCounts) ? array_key_first($scoreCounts) : null;

        $avgGoals = $totalPlayed > 0 ? round(($homeGoalsTotal + $awayGoalsTotal) / $totalPlayed, 1) : 0.0;
        $bttsPct = $totalPlayed > 0 ? round(($bttsCount / $totalPlayed) * 100, 1) : 0.0;
        $over25Pct = $totalPlayed > 0 ? round(($over25Count / $totalPlayed) * 100, 1) : 0.0;

        return self::$h2hCache[$cacheKey] = [
            'matches' => $allH2H->take($limit),
            'total_played' => $totalPlayed,
            'home_wins' => $homeWins,
            'draws' => $draws,
            'away_wins' => $awayWins,
            'home_win_pct' => $totalPlayed > 0 ? round(($homeWins / $totalPlayed) * 100, 1) : 0.0,
            'draw_pct' => $totalPlayed > 0 ? round(($draws / $totalPlayed) * 100, 1) : 0.0,
            'away_win_pct' => $totalPlayed > 0 ? round(($awayWins / $totalPlayed) * 100, 1) : 0.0,
            'home_goals_total' => $homeGoalsTotal,
            'away_goals_total' => $awayGoalsTotal,
            'avg_goals' => $avgGoals,
            'btts_count' => $bttsCount,
            'btts_pct' => $bttsPct,
            'over_25_count' => $over25Count,
            'over_25_pct' => $over25Pct,
            'clean_sheets_home' => $cleanSheetsHome,
            'clean_sheets_away' => $cleanSheetsAway,
            'most_frequent_score' => $mostFrequentScore,
        ];
    }

    /**
     * Retrieve Man of the Match, Team Ratings, and Individual Player Ratings.
     * Evaluates dynamically if missing.
     */
    public function getPerformanceDetails(FootballMatch $match, bool $autoEvaluate = true): array
    {
        $perfKey = (int) $match->id;
        if ($perfKey > 0 && isset(self::$perfCache[$perfKey])) {
            return self::$perfCache[$perfKey];
        }

        $evaluations = $match->relationLoaded('motmEvaluations')
            ? $match->motmEvaluations
            : $match->motmEvaluations()->with('team')->get();

        if ($evaluations->isEmpty() && $autoEvaluate && $match->home_team_id && $match->away_team_id) {
            try {
                $evaluations = $this->motmEvaluator->evaluateMatch($match);
            } catch (\Throwable) {
                $evaluations = collect();
            }
        }

        if ($evaluations->isEmpty()) {
            $empty = [
                'has_evaluations' => false,
                'motm' => null,
                'home_players' => collect(),
                'away_players' => collect(),
                'home_team_rating' => null,
                'away_team_rating' => null,
                'home_units' => ['attack' => null, 'midfield' => null, 'defense' => null],
                'away_units' => ['attack' => null, 'midfield' => null, 'defense' => null],
                'goals' => [
                    'home_scorers' => collect(),
                    'away_scorers' => collect(),
                    'home_assists' => collect(),
                    'away_assists' => collect(),
                ],
            ];
            if ($perfKey > 0) {
                self::$perfCache[$perfKey] = $empty;
            }
            return $empty;
        }

        $motm = $evaluations->firstWhere('is_motm', true) ?? $evaluations->sortByDesc('rating')->first();

        $homePlayers = $evaluations->where('team_id', $match->home_team_id)->sortByDesc('rating')->values();
        $awayPlayers = $evaluations->where('team_id', $match->away_team_id)->sortByDesc('rating')->values();

        $homeTeamRating = $homePlayers->isNotEmpty() ? round((float) $homePlayers->avg('rating'), 1) : null;
        $awayTeamRating = $awayPlayers->isNotEmpty() ? round((float) $awayPlayers->avg('rating'), 1) : null;

        $calcUnits = function (Collection $players): array {
            $atk = $players->filter(fn ($p) => in_array(strtoupper((string) $p->position), ['F', 'FWD'], true) || ($p->role ?? '') === 'striker' || ($p->role ?? '') === 'winger');
            $mid = $players->filter(fn ($p) => in_array(strtoupper((string) $p->position), ['M', 'MID'], true) || ($p->role ?? '') === 'playmaker' || ($p->role ?? '') === 'anchor');
            $def = $players->filter(fn ($p) => in_array(strtoupper((string) $p->position), ['D', 'DEF', 'GK', 'G'], true) || ($p->role ?? '') === 'centerback' || ($p->role ?? '') === 'fullback' || ($p->role ?? '') === 'goalkeeper');

            return [
                'attack' => $atk->isNotEmpty() ? round((float) $atk->avg('rating'), 1) : null,
                'midfield' => $mid->isNotEmpty() ? round((float) $mid->avg('rating'), 1) : null,
                'defense' => $def->isNotEmpty() ? round((float) $def->avg('rating'), 1) : null,
            ];
        };

        $homeUnits = $calcUnits($homePlayers);
        $awayUnits = $calcUnits($awayPlayers);

        $homeScorers = $homePlayers->filter(fn ($p) => (int) $p->goals > 0);
        $awayScorers = $awayPlayers->filter(fn ($p) => (int) $p->goals > 0);
        $homeAssists = $homePlayers->filter(fn ($p) => (int) $p->assists > 0);
        $awayAssists = $awayPlayers->filter(fn ($p) => (int) $p->assists > 0);

        return self::$perfCache[$perfKey] = [
            'has_evaluations' => true,
            'motm' => $motm,
            'home_players' => $homePlayers,
            'away_players' => $awayPlayers,
            'home_team_rating' => $homeTeamRating,
            'away_team_rating' => $awayTeamRating,
            'home_units' => $homeUnits,
            'away_units' => $awayUnits,
            'goals' => [
                'home_scorers' => $homeScorers,
                'away_scorers' => $awayScorers,
                'home_assists' => $homeAssists,
                'away_assists' => $awayAssists,
            ],
        ];
    }

    /**
     * Retrieve comprehensive package containing both H2H history and performance details.
     */
    public function getDetailsForMatch(FootballMatch $match): array
    {
        return [
            'h2h' => $this->getHeadToHead($match),
            'performance' => $this->getPerformanceDetails($match),
        ];
    }
}
