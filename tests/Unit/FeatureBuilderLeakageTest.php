<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Competition;
use App\Models\FootballMatch;
use App\Models\Team;
use App\Services\Features\FeatureBuilder;
use Carbon\CarbonImmutable;
use Database\Seeders\CompetitionSeeder;
use Database\Seeders\TeamSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeatureBuilderLeakageTest extends TestCase
{
    use RefreshDatabase;

    protected FeatureBuilder $builder;
    protected Team $arsenal;
    protected Team $chelsea;
    protected Competition $pl;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CompetitionSeeder::class);
        $this->seed(TeamSeeder::class);

        $this->builder = new FeatureBuilder();
        $this->arsenal = Team::where('name', 'Arsenal FC')->first();
        $this->chelsea = Team::where('name', 'Chelsea FC')->first();
        $this->pl = Competition::where('slug', 'premier-league')->first();
    }

    /**
     * MANDATORY LEAKAGE TEST (AGENTS.md Section 5 / Rule 1)
     * Proves that adding or altering a match dated AFTER asOf does NOT change features as of that asOf.
     */
    public function test_matches_after_as_of_do_not_leak_into_features(): void
    {
        $asOf = CarbonImmutable::parse('2024-11-01 12:00:00', 'UTC');

        // 1. Create historical matches strictly BEFORE asOf
        FootballMatch::create([
            'competition_id' => $this->pl->id,
            'season' => 2024,
            'matchday' => 1,
            'kickoff_at' => $asOf->subDays(20),
            'home_team_id' => $this->arsenal->id,
            'away_team_id' => $this->chelsea->id,
            'status' => 'finished',
            'home_goals' => 3,
            'away_goals' => 1,
        ]);

        FootballMatch::create([
            'competition_id' => $this->pl->id,
            'season' => 2024,
            'matchday' => 2,
            'kickoff_at' => $asOf->subDays(10),
            'home_team_id' => $this->chelsea->id,
            'away_team_id' => $this->arsenal->id,
            'status' => 'finished',
            'home_goals' => 0,
            'away_goals' => 2,
        ]);

        // Compute features before adding future matches
        $baselineFeatures = $this->builder->build($this->arsenal, $asOf);
        $baselineArray = $baselineFeatures->toArray();

        // 2. Insert a FUTURE match (dated strictly AFTER asOf) with extreme scoreline
        FootballMatch::create([
            'competition_id' => $this->pl->id,
            'season' => 2024,
            'matchday' => 3,
            'kickoff_at' => $asOf->addDays(5), // IN THE FUTURE
            'home_team_id' => $this->arsenal->id,
            'away_team_id' => $this->chelsea->id,
            'status' => 'finished',
            'home_goals' => 9, // Extreme score that would alter averages drastically if leaked
            'away_goals' => 0,
        ]);

        // 3. Re-compute features for the SAME asOf
        $postFutureFeatures = $this->builder->build($this->arsenal, $asOf);
        $postFutureArray = $postFutureFeatures->toArray();

        // ASSERT ZERO LEAKAGE: Features must remain 100% IDENTICAL
        $this->assertEquals($baselineArray['home_attack_strength'], $postFutureArray['home_attack_strength']);
        $this->assertEquals($baselineArray['home_defence_strength'], $postFutureArray['home_defence_strength']);
        $this->assertEquals($baselineArray['away_attack_strength'], $postFutureArray['away_attack_strength']);
        $this->assertEquals($baselineArray['away_defence_strength'], $postFutureArray['away_defence_strength']);
        $this->assertEquals($baselineArray['home_xg_attack_strength'], $postFutureArray['home_xg_attack_strength']);
        $this->assertEquals($baselineArray['home_xg_defence_strength'], $postFutureArray['home_xg_defence_strength']);
        $this->assertEquals($baselineArray['away_xg_attack_strength'], $postFutureArray['away_xg_attack_strength']);
        $this->assertEquals($baselineArray['away_xg_defence_strength'], $postFutureArray['away_xg_defence_strength']);
        $this->assertEquals($baselineArray['xg_sample_count'], $postFutureArray['xg_sample_count']);
        $this->assertEquals($baselineArray['matches_sample_count'], $postFutureArray['matches_sample_count']);
        $this->assertEquals($baselineArray['form_points_last_5'], $postFutureArray['form_points_last_5']);
        $this->assertEquals($baselineArray['league_avg_home_goals'], $postFutureArray['league_avg_home_goals']);
        $this->assertEquals($baselineArray['league_avg_away_goals'], $postFutureArray['league_avg_away_goals']);
        $this->assertEquals($baselineArray, $postFutureArray);
    }

    public function test_xg_stats_after_as_of_do_not_leak_into_features(): void
    {
        $asOf = CarbonImmutable::parse('2024-11-01 12:00:00', 'UTC');

        // Past match with xG stat
        $pastMatch = FootballMatch::create([
            'competition_id' => $this->pl->id,
            'season' => 2024,
            'matchday' => 1,
            'kickoff_at' => $asOf->subDays(15),
            'home_team_id' => $this->arsenal->id,
            'away_team_id' => $this->chelsea->id,
            'status' => 'finished',
            'home_goals' => 2,
            'away_goals' => 1,
        ]);

        \App\Models\MatchStat::create([
            'match_id' => $pastMatch->id,
            'team_id' => $this->arsenal->id,
            'source' => 'understat',
            'xg' => 1.85,
            'xga' => 0.95,
        ]);

        $beforeFuture = $this->builder->build($this->arsenal, $asOf);

        // Future match with extreme xG
        $futureMatch = FootballMatch::create([
            'competition_id' => $this->pl->id,
            'season' => 2024,
            'matchday' => 2,
            'kickoff_at' => $asOf->addDays(5),
            'home_team_id' => $this->arsenal->id,
            'away_team_id' => $this->chelsea->id,
            'status' => 'finished',
            'home_goals' => 5,
            'away_goals' => 0,
        ]);

        \App\Models\MatchStat::create([
            'match_id' => $futureMatch->id,
            'team_id' => $this->arsenal->id,
            'source' => 'understat',
            'xg' => 6.50,
            'xga' => 0.10,
        ]);

        $afterFuture = $this->builder->build($this->arsenal, $asOf);

        $this->assertEquals(
            $beforeFuture->homeXgAttackStrength,
            $afterFuture->homeXgAttackStrength
        );
        $this->assertEquals(
            $beforeFuture->homeXgDefenceStrength,
            $afterFuture->homeXgDefenceStrength
        );
        $this->assertEquals(
            $beforeFuture->xgSampleCount,
            $afterFuture->xgSampleCount
        );
    }

    /**
     * MANDATORY LEAKAGE TEST: Head-to-Head (Tier 3)
     * Proves that matches dated strictly AFTER asOf do NOT affect H2H modifier calculation.
     */
    public function test_h2h_matches_after_as_of_do_not_leak(): void
    {
        $asOf = CarbonImmutable::parse('2024-11-01 12:00:00', 'UTC');
        $predictionService = app(\App\Services\Predictions\PredictionService::class);

        // 1. Historical meeting before asOf
        FootballMatch::create([
            'competition_id' => $this->pl->id,
            'season' => 2024,
            'matchday' => 1,
            'kickoff_at' => $asOf->subDays(30),
            'home_team_id' => $this->arsenal->id,
            'away_team_id' => $this->chelsea->id,
            'status' => 'finished',
            'home_goals' => 2,
            'away_goals' => 1,
        ]);

        FootballMatch::create([
            'competition_id' => $this->pl->id,
            'season' => 2024,
            'matchday' => 2,
            'kickoff_at' => $asOf->subDays(15),
            'home_team_id' => $this->chelsea->id,
            'away_team_id' => $this->arsenal->id,
            'status' => 'finished',
            'home_goals' => 1,
            'away_goals' => 1,
        ]);

        $beforeFutureH2H = $predictionService->calculateH2HModifier($this->arsenal->id, $this->chelsea->id, $asOf);

        // 2. Future meeting after asOf
        FootballMatch::create([
            'competition_id' => $this->pl->id,
            'season' => 2024,
            'matchday' => 3,
            'kickoff_at' => $asOf->addDays(10), // FUTURE
            'home_team_id' => $this->arsenal->id,
            'away_team_id' => $this->chelsea->id,
            'status' => 'finished',
            'home_goals' => 10,
            'away_goals' => 0,
        ]);

        $afterFutureH2H = $predictionService->calculateH2HModifier($this->arsenal->id, $this->chelsea->id, $asOf);

        $this->assertEquals($beforeFutureH2H, $afterFutureH2H);
    }
}

