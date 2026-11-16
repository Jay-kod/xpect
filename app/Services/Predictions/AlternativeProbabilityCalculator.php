<?php

declare(strict_types=1);

namespace App\Services\Predictions;

use App\Models\FootballMatch;
use Carbon\CarbonImmutable;

class AlternativeProbabilityCalculator
{
    public const SYSTEM_VERSION = 'v1.0-elo';

    /**
     * @var array<string, array<int, float>> In-memory cache by compId_cutoffDate
     */
    protected array $ratingsCache = [];

    /**
     * Calculate independent Elo & Empirical probabilities strictly before asOf.
     * Zero data leakage: only matches with kickoff_at < asOf and status = finished.
     *
     * @return array{
     *     system: string,
     *     p_home: float,
     *     p_draw: float,
     *     p_away: float,
     *     home_rating: float,
     *     away_rating: float,
     *     rating_diff: float,
     *     top_pick: string,
     *     top_pick_label: string,
     *     h2h_matches_analyzed: int,
     *     agreement_with_poisson: ?bool
     * }
     */
    public function calculate(FootballMatch $match, ?CarbonImmutable $asOf = null, ?float $poissonPHome = null, ?float $poissonPAway = null): array
    {
        $cutoff = $asOf ?? ($match->kickoff_at ? CarbonImmutable::parse($match->kickoff_at) : CarbonImmutable::now('UTC'));

        $homeTeamId = $match->home_team_id;
        $awayTeamId = $match->away_team_id;
        $competitionId = $match->competition_id;

        // 1. Calculate dynamic Elo ratings for both teams from past finished matches in this league before asOf
        $ratings = $this->calculateTeamEloRatings($competitionId, $cutoff);
        $homeElo = $ratings[$homeTeamId] ?? 1500.0;
        $awayElo = $ratings[$awayTeamId] ?? 1500.0;

        // Home field advantage bonus (typically 65 Elo points in top European leagues)
        $homeAdvantage = 65.0;
        $ratingDiff = ($homeElo + $homeAdvantage) - $awayElo;

        // 2. Logistic win expectancy (Bradley-Terry / Elo formula)
        // E_home = 1 / (1 + 10^(-ratingDiff / 400))
        $expHome = 1.0 / (1.0 + (10.0 ** (-$ratingDiff / 400.0)));
        $expAway = 1.0 - $expHome;

        // 3. Dynamic draw probability based on rating parity
        // High parity (|ratingDiff| near 0) -> higher draw chance (~26-27%); large rating gap -> lower draw chance (~18-20%)
        $baseDrawRate = 0.255;
        $drawFactor = exp(-(($ratingDiff / 450.0) ** 2));
        $pDrawRaw = max(0.16, min(0.30, $baseDrawRate * $drawFactor));

        // 4. Allocate remaining probability mass between Home and Away proportionally to logistic expectations
        $remainingMass = 1.0 - $pDrawRaw;
        $pHomeRaw = $remainingMass * $expHome;
        $pAwayRaw = $remainingMass * $expAway;

        // 5. Empirical Head-to-Head Bayesian adjustment (strictly before asOf)
        $h2hMatches = FootballMatch::where('status', 'finished')
            ->where('kickoff_at', '<', $cutoff)
            ->whereNotNull('home_goals')
            ->whereNotNull('away_goals')
            ->where(function ($q) use ($homeTeamId, $awayTeamId) {
                $q->where(fn ($sub) => $sub->where('home_team_id', $homeTeamId)->where('away_team_id', $awayTeamId))
                  ->orWhere(fn ($sub) => $sub->where('home_team_id', $awayTeamId)->where('away_team_id', $homeTeamId));
            })
            ->orderBy('kickoff_at', 'desc')
            ->limit(8)
            ->get();

        $h2hCount = $h2hMatches->count();
        if ($h2hCount >= 3) {
            $h2hHomeWins = 0;
            $h2hDraws = 0;
            $h2hAwayWins = 0;

            foreach ($h2hMatches as $m) {
                $isMatchHome = ($m->home_team_id === $homeTeamId);
                if ($m->home_goals > $m->away_goals) {
                    $isMatchHome ? $h2hHomeWins++ : $h2hAwayWins++;
                } elseif ($m->home_goals < $m->away_goals) {
                    $isMatchHome ? $h2hAwayWins++ : $h2hHomeWins++;
                } else {
                    $h2hDraws++;
                }
            }

            // Empirical H2H probabilities with Laplace smoothing (+1 pseudocount)
            $empH = ($h2hHomeWins + 1.0) / ($h2hCount + 3.0);
            $empD = ($h2hDraws + 1.0) / ($h2hCount + 3.0);
            $empA = ($h2hAwayWins + 1.0) / ($h2hCount + 3.0);

            // Blend Elo probability (80%) with direct empirical H2H record (20%)
            $pHomeRaw = (0.80 * $pHomeRaw) + (0.20 * $empH);
            $pDrawRaw = (0.80 * $pDrawRaw) + (0.20 * $empD);
            $pAwayRaw = (0.80 * $pAwayRaw) + (0.20 * $empA);
        }

        // Normalize sum to exactly 1.0
        $sum = $pHomeRaw + $pDrawRaw + $pAwayRaw;
        $pHome = round($pHomeRaw / $sum, 4);
        $pDraw = round($pDrawRaw / $sum, 4);
        $pAway = round(1.0 - $pHome - $pDraw, 4);

        // Determine top pick
        $topPick = 'home';
        $topPickLabel = ($match->homeTeam?->short_name ?? 'Home') . ' Win';
        if ($pDraw > $pHome && $pDraw >= $pAway) {
            $topPick = 'draw';
            $topPickLabel = 'Draw';
        } elseif ($pAway > $pHome && $pAway > $pDraw) {
            $topPick = 'away';
            $topPickLabel = ($match->awayTeam?->short_name ?? 'Away') . ' Win';
        }

        // Agreement with Poisson (System 1) if provided
        $agreementWithPoisson = null;
        if ($poissonPHome !== null && $poissonPAway !== null) {
            $poissonPick = 'home';
            $poissonPDraw = max(0, 1.0 - $poissonPHome - $poissonPAway);
            if ($poissonPDraw > $poissonPHome && $poissonPDraw >= $poissonPAway) {
                $poissonPick = 'draw';
            } elseif ($poissonPAway > $poissonPHome && $poissonPAway > $poissonPDraw) {
                $poissonPick = 'away';
            }
            $agreementWithPoisson = ($topPick === $poissonPick);
        }

        return [
            'system' => self::SYSTEM_VERSION,
            'p_home' => $pHome,
            'p_draw' => $pDraw,
            'p_away' => $pAway,
            'home_rating' => round($homeElo, 1),
            'away_rating' => round($awayElo, 1),
            'rating_diff' => round($ratingDiff, 1),
            'top_pick' => $topPick,
            'top_pick_label' => $topPickLabel,
            'h2h_matches_analyzed' => $h2hCount,
            'agreement_with_poisson' => $agreementWithPoisson,
        ];
    }

    /**
     * Compute dynamic Elo ratings for teams in a competition strictly before cutoff.
     * Uses rolling walk-forward updates with learned parameters if calibrated. Zero data leakage.
     *
     * @return array<int, float> Team ID => Elo Rating
     */
    public function calculateTeamEloRatings(int $competitionId, CarbonImmutable $cutoff): array
    {
        $cacheKey = "{$competitionId}_" . $cutoff->toDateString();
        if (isset($this->ratingsCache[$cacheKey])) {
            return $this->ratingsCache[$cacheKey];
        }

        $pastMatches = FootballMatch::where('competition_id', $competitionId)
            ->where('status', 'finished')
            ->where('kickoff_at', '<', $cutoff)
            ->whereNotNull('home_goals')
            ->whereNotNull('away_goals')
            ->orderBy('kickoff_at', 'asc')
            ->get(['home_team_id', 'away_team_id', 'home_goals', 'away_goals']);

        $ratings = [];
        $k = 32.0;
        $homeAdvantage = 65.0;

        // Check if calibrated weights exist from learning pipeline
        $calibFile = storage_path('calibration/calibrated_weights.json');
        if (file_exists($calibFile)) {
            $data = json_decode((string) file_get_contents($calibFile), true);
            if (isset($data['best_parameters']['k_factor'])) {
                $k = (float) $data['best_parameters']['k_factor'];
            }
            if (isset($data['best_parameters']['home_adv'])) {
                $homeAdvantage = (float) $data['best_parameters']['home_adv'];
            }
        }

        foreach ($pastMatches as $m) {
            $hId = (int) $m->home_team_id;
            $aId = (int) $m->away_team_id;

            $rH = $ratings[$hId] ?? 1500.0;
            $rA = $ratings[$aId] ?? 1500.0;

            // Actual outcome score (Home perspective: 1.0 Win, 0.5 Draw, 0.0 Loss)
            $actualH = match (true) {
                $m->home_goals > $m->away_goals => 1.0,
                $m->home_goals < $m->away_goals => 0.0,
                default => 0.5,
            };
            $actualA = 1.0 - $actualH;

            // Expected score with home advantage
            $expH = 1.0 / (1.0 + (10.0 ** (-($rH + $homeAdvantage - $rA) / 400.0)));
            $expA = 1.0 - $expH;

            // Goal margin multiplier: larger wins adjust ratings slightly more
            $goalDiff = abs($m->home_goals - $m->away_goals);
            $marginMultiplier = match ($goalDiff) {
                0, 1 => 1.0,
                2 => 1.25,
                default => 1.50,
            };

            $ratings[$hId] = $rH + ($k * $marginMultiplier * ($actualH - $expH));
            $ratings[$aId] = $rA + ($k * $marginMultiplier * ($actualA - $expA));
        }

        $this->ratingsCache[$cacheKey] = $ratings;
        return $ratings;
    }

    /**
     * Calculate 3-System Ensemble Consensus (Poisson Engine + Elo Engine).
     *
     * @return array{
     *     consensus_p_home: float,
     *     consensus_p_draw: float,
     *     consensus_p_away: float,
     *     consensus_pick: string,
     *     consensus_pick_label: string,
     *     confidence_level: string,
     *     status_badge: string,
     *     divergence_description: string
     * }
     */
    public function calculateConsensus(
        float $p1Home,
        float $p1Draw,
        float $p1Away,
        float $p2Home,
        float $p2Draw,
        float $p2Away,
        string $homeClubName = 'Home',
        string $awayClubName = 'Away'
    ): array {
        // Equal 50/50 precision ensemble blend
        $cH = round(($p1Home + $p2Home) / 2.0, 4);
        $cD = round(($p1Draw + $p2Draw) / 2.0, 4);
        $cA = round(1.0 - $cH - $cD, 4);

        $pick = 'home';
        $label = "{$homeClubName} Win";
        if ($cD > $cH && $cD >= $cA) {
            $pick = 'draw';
            $label = 'Draw';
        } elseif ($cA > $cH && $cA > $cD) {
            $pick = 'away';
            $label = "{$awayClubName} Win";
        }

        // Determine if models agree or diverge
        $p1Pick = ($p1Home >= $p1Draw && $p1Home >= $p1Away) ? 'home' : (($p1Away > $p1Home && $p1Away > $p1Draw) ? 'away' : 'draw');
        $p2Pick = ($p2Home >= $p2Draw && $p2Home >= $p2Away) ? 'home' : (($p2Away > $p2Home && $p2Away > $p2Draw) ? 'away' : 'draw');

        if ($p1Pick === $p2Pick) {
            $confidence = 'High Agreement';
            $statusBadge = '✓ UNIFIED CONSENSUS';
            $desc = "Both System 1 (Poisson) and System 2 (Elo) agree on {$label}.";
        } else {
            $confidence = 'Divergent Perspectives';
            $statusBadge = '⚡ MULTI-PERSPECTIVE DIVERGENCE';
            $p1Name = match ($p1Pick) { 'home' => "{$homeClubName} Win", 'away' => "{$awayClubName} Win", default => 'Draw' };
            $p2Name = match ($p2Pick) { 'home' => "{$homeClubName} Win", 'away' => "{$awayClubName} Win", default => 'Draw' };
            $desc = "System 1 (Poisson) leans toward {$p1Name}, whereas System 2 (Elo) favors {$p2Name}.";
        }

        return [
            'consensus_p_home' => $cH,
            'consensus_p_draw' => $cD,
            'consensus_p_away' => $cA,
            'consensus_pick' => $pick,
            'consensus_pick_label' => $label,
            'confidence_level' => $confidence,
            'status_badge' => $statusBadge,
            'divergence_description' => $desc,
        ];
    }
}
