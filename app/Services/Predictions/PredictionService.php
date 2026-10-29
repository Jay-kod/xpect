<?php

declare(strict_types=1);

namespace App\Services\Predictions;

use App\Models\FootballMatch;
use App\Models\Injury;
use App\Models\Prediction;
use App\Services\Features\FeatureBuilder;
use App\Services\Models\PoissonModel;
use Carbon\CarbonImmutable;

class PredictionService
{
    public function __construct(
        protected readonly FeatureBuilder $featureBuilder,
        protected readonly PoissonModel $poissonModel
    ) {
    }

    /**
     * Generate and store an immutable prediction for a match.
     */
    public function predict(
        FootballMatch $match,
        ?CarbonImmutable $asOf = null,
        bool $isBacktest = false,
        string $modelVersion = PoissonModel::MODEL_VERSION,
        string $stage = 'pre_match'
    ): Prediction {
        // Cutoff MUST be strictly <= kickoff
        $cutoff = $asOf ?? CarbonImmutable::parse($match->kickoff_at);

        $homeFeatures = $this->featureBuilder->build($match->homeTeam, $cutoff);
        $awayFeatures = $this->featureBuilder->build($match->awayTeam, $cutoff);

        $homeInjuries = Injury::where('team_id', $match->home_team_id)
            ->where('as_of', '<=', $cutoff)
            ->where('as_of', '>=', $cutoff->subDays(14))
            ->count();

        $awayInjuries = Injury::where('team_id', $match->away_team_id)
            ->where('as_of', '<=', $cutoff)
            ->where('as_of', '>=', $cutoff->subDays(14))
            ->count();

        $lineupsConfirmed = $match->lineups()->where('confirmed', true)->count() >= 2;

        $h2hModifier = null;
        if ($modelVersion === PoissonModel::MODEL_V1_H2H) {
            $h2hModifier = $this->calculateH2HModifier($match->home_team_id, $match->away_team_id, $cutoff);
        }

        $probabilities = $this->poissonModel->predict(
            $homeFeatures,
            $awayFeatures,
            $modelVersion,
            $h2hModifier
        );

        // Matchday / final injury adjustment if stage is matchday or final
        if ($stage !== 'pre_match' && ($homeInjuries > 0 || $awayInjuries > 0)) {
            $homePenalty = max(0.88, 1.0 - ($homeInjuries * 0.03));
            $awayPenalty = max(0.88, 1.0 - ($awayInjuries * 0.03));

            $adjExpHome = $probabilities->expHomeGoals * $homePenalty;
            $adjExpAway = $probabilities->expAwayGoals * $awayPenalty;

            // Simple goal ratio shift for matchday probability update
            $tot = $adjExpHome + $adjExpAway;
            if ($tot > 0) {
                $pHomeAdj = max(0.05, min(0.90, $probabilities->pHome * ($adjExpHome / max(0.1, $probabilities->expHomeGoals))));
                $pAwayAdj = max(0.05, min(0.90, $probabilities->pAway * ($adjExpAway / max(0.1, $probabilities->expAwayGoals))));
                $pDrawAdj = max(0.05, 1.0 - $pHomeAdj - $pAwayAdj);

                // Re-normalize sum to 1.0
                $sum = $pHomeAdj + $pDrawAdj + $pAwayAdj;
                $pHome = round($pHomeAdj / $sum, 4);
                $pDraw = round($pDrawAdj / $sum, 4);
                $pAway = round(1.0 - $pHome - $pDraw, 4);
            } else {
                $pHome = $probabilities->pHome;
                $pDraw = $probabilities->pDraw;
                $pAway = $probabilities->pAway;
            }
            $expHomeGoals = $adjExpHome;
            $expAwayGoals = $adjExpAway;
        } else {
            $pHome = $probabilities->pHome;
            $pDraw = $probabilities->pDraw;
            $pAway = $probabilities->pAway;
            $expHomeGoals = $probabilities->expHomeGoals;
            $expAwayGoals = $probabilities->expAwayGoals;
        }

        return Prediction::create([
            'match_id' => $match->id,
            'model_version' => $modelVersion,
            'stage' => $stage,
            'is_backtest' => $isBacktest,
            'as_of' => $cutoff,
            'p_home' => $pHome,
            'p_draw' => $pDraw,
            'p_away' => $pAway,
            'exp_home_goals' => $expHomeGoals,
            'exp_away_goals' => $expAwayGoals,
            'top_scorelines' => $probabilities->topScorelines,
            'features' => [
                'home' => $homeFeatures->toArray(),
                'away' => $awayFeatures->toArray(),
                'stage' => $stage,
                'injuries' => [
                    'home_count' => $homeInjuries,
                    'away_count' => $awayInjuries,
                ],
                'lineups_confirmed' => $lineupsConfirmed,
            ],
            'low_confidence' => $lineupsConfirmed ? false : ($homeFeatures->lowConfidence || $awayFeatures->lowConfidence),
        ]);
    }

    /**
     * Compute shrinkage-adjusted Head-to-Head goal ratio modifier strictly before asOf.
     * Zero data leakage: only finished matches with kickoff_at < asOf.
     */
    public function calculateH2HModifier(int $homeTeamId, int $awayTeamId, CarbonImmutable $asOf): float
    {
        $h2hMatches = FootballMatch::where('status', 'finished')
            ->where('kickoff_at', '<', $asOf)
            ->whereNotNull('home_goals')
            ->whereNotNull('away_goals')
            ->where(function ($q) use ($homeTeamId, $awayTeamId) {
                $q->where(fn ($sub) => $sub->where('home_team_id', $homeTeamId)->where('away_team_id', $awayTeamId))
                  ->orWhere(fn ($sub) => $sub->where('home_team_id', $awayTeamId)->where('away_team_id', $homeTeamId));
            })
            ->orderBy('kickoff_at', 'desc')
            ->limit(6)
            ->get();

        if ($h2hMatches->count() < 2) {
            return 1.0;
        }

        $homeGoals = 0;
        $awayGoals = 0;
        foreach ($h2hMatches as $m) {
            if ($m->home_team_id === $homeTeamId) {
                $homeGoals += $m->home_goals;
                $awayGoals += $m->away_goals;
            } else {
                $homeGoals += $m->away_goals;
                $awayGoals += $m->home_goals;
            }
        }

        $count = $h2hMatches->count();
        $rawRatio = ($homeGoals + 1.0) / ($awayGoals + 1.0);
        $k = 4.0;
        $shrunk = ($count * $rawRatio + $k) / ($count + $k);

        return max(0.85, min(1.15, $shrunk));
    }
}

