<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Data\TeamFeatures;
use App\Services\Models\PoissonModel;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;

class PoissonModelTest extends TestCase
{
    protected PoissonModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new PoissonModel();
    }

    /**
     * Hand-calculated Poisson probabilities: P(X=k) = (lambda^k * e^-lambda) / k!
     * 
     * For lambda = 1.0:
     * P(0) = e^-1               = 0.36787944117
     * P(1) = 1.0 * e^-1         = 0.36787944117
     * P(2) = (1.0 / 2) * e^-1   = 0.18393972058
     * P(3) = (1.0 / 6) * e^-1   = 0.06131324019
     * 
     * For lambda = 2.0:
     * P(0) = e^-2               = 0.13533528323
     * P(1) = 2.0 * e^-2         = 0.27067056647
     * P(2) = (4.0 / 2) * e^-2   = 0.27067056647
     * P(3) = (8.0 / 6) * e^-2   = 0.18044704431
     */
    public function test_hand_checked_poisson_probabilities(): void
    {
        // Lambda = 1.0 tests
        $this->assertEqualsWithDelta(0.367879, $this->model->poissonProbability(1.0, 0), 0.0001);
        $this->assertEqualsWithDelta(0.367879, $this->model->poissonProbability(1.0, 1), 0.0001);
        $this->assertEqualsWithDelta(0.183940, $this->model->poissonProbability(1.0, 2), 0.0001);
        $this->assertEqualsWithDelta(0.061313, $this->model->poissonProbability(1.0, 3), 0.0001);

        // Lambda = 2.0 tests
        $this->assertEqualsWithDelta(0.135335, $this->model->poissonProbability(2.0, 0), 0.0001);
        $this->assertEqualsWithDelta(0.270671, $this->model->poissonProbability(2.0, 1), 0.0001);
        $this->assertEqualsWithDelta(0.270671, $this->model->poissonProbability(2.0, 2), 0.0001);
        $this->assertEqualsWithDelta(0.180447, $this->model->poissonProbability(2.0, 3), 0.0001);
    }

    public function test_probabilities_sum_to_one_and_match_team_strength_gradient(): void
    {
        $asOf = CarbonImmutable::parse('2024-10-01 15:00:00', 'UTC');

        // Strong home team vs weak away team
        $strongHome = new TeamFeatures(
            teamId: 1,
            teamName: 'Strong Home FC',
            asOf: $asOf,
            homeAttackStrength: 1.40,
            homeDefenceStrength: 0.70,
            awayAttackStrength: 1.20,
            awayDefenceStrength: 0.80,
            matchesSampleCount: 20,
            lowConfidence: false,
            formPointsLast5: 12,
            restDays: 6,
            leagueAvgHomeGoals: 1.50,
            leagueAvgAwayGoals: 1.20,
        );

        $weakAway = new TeamFeatures(
            teamId: 2,
            teamName: 'Weak Away FC',
            asOf: $asOf,
            homeAttackStrength: 0.90,
            homeDefenceStrength: 1.20,
            awayAttackStrength: 0.70,
            awayDefenceStrength: 1.50,
            matchesSampleCount: 20,
            lowConfidence: false,
            formPointsLast5: 3,
            restDays: 6,
            leagueAvgHomeGoals: 1.50,
            leagueAvgAwayGoals: 1.20,
        );

        $probs = $this->model->predict($strongHome, $weakAway);

        // 1. Probabilities must sum to 1.0 (within float precision)
        $sum = $probs->pHome + $probs->pDraw + $probs->pAway;
        $this->assertEqualsWithDelta(1.0, $sum, 0.0001);

        // 2. Strong home vs weak away should heavily favor Home Win
        $this->assertGreaterThan($probs->pAway, $probs->pHome);
        $this->assertGreaterThan(0.50, $probs->pHome); // Strong home should have > 50% win probability

        // 3. Expected goals: Home exp goals should exceed Away exp goals
        $this->assertGreaterThan($probs->expAwayGoals, $probs->expHomeGoals);

        // 4. Top scoreline check
        $this->assertCount(5, $probs->topScorelines);
        $this->assertIsArray($probs->mostLikelyScore);
        $this->assertNotEmpty($probs->mostLikelyScore['score']);
    }

    public function test_dixon_coles_adjusts_low_score_probabilities(): void
    {
        $asOf = CarbonImmutable::parse('2024-10-01 15:00:00', 'UTC');

        $home = new TeamFeatures(
            teamId: 1,
            teamName: 'Home FC',
            asOf: $asOf,
            homeAttackStrength: 1.0,
            homeDefenceStrength: 1.0,
            awayAttackStrength: 1.0,
            awayDefenceStrength: 1.0,
            matchesSampleCount: 20,
            lowConfidence: false,
            formPointsLast5: 7,
            restDays: 7,
            leagueAvgHomeGoals: 1.40,
            leagueAvgAwayGoals: 1.10,
            homeXgAttackStrength: 1.0,
            homeXgDefenceStrength: 1.0,
            awayXgAttackStrength: 1.0,
            awayXgDefenceStrength: 1.0,
            xgSampleCount: 20,
        );

        $away = new TeamFeatures(
            teamId: 2,
            teamName: 'Away FC',
            asOf: $asOf,
            homeAttackStrength: 1.0,
            homeDefenceStrength: 1.0,
            awayAttackStrength: 1.0,
            awayDefenceStrength: 1.0,
            matchesSampleCount: 20,
            lowConfidence: false,
            formPointsLast5: 7,
            restDays: 7,
            leagueAvgHomeGoals: 1.40,
            leagueAvgAwayGoals: 1.10,
            homeXgAttackStrength: 1.0,
            homeXgDefenceStrength: 1.0,
            awayXgAttackStrength: 1.0,
            awayXgDefenceStrength: 1.0,
            xgSampleCount: 20,
        );

        $stdProbs = $this->model->predict($home, $away, PoissonModel::MODEL_V1_XG);
        $dcProbs = $this->model->predict($home, $away, PoissonModel::MODEL_V1_DIXON_COLES);

        // Under Dixon-Coles with negative rho (-0.06), 0-0 and 1-1 draws receive a boost
        $std00 = $stdProbs->scoreMatrix[0][0];
        $dc00 = $dcProbs->scoreMatrix[0][0];
        $this->assertGreaterThan($std00, $dc00);

        $std11 = $stdProbs->scoreMatrix[1][1];
        $dc11 = $dcProbs->scoreMatrix[1][1];
        $this->assertGreaterThan($std11, $dc11);

        // Probabilities must still sum to 1.0
        $sum = $dcProbs->pHome + $dcProbs->pDraw + $dcProbs->pAway;
        $this->assertEqualsWithDelta(1.0, $sum, 0.0001);
    }
}

