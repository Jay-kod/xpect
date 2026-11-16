<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Competition;
use App\Models\FootballMatch;
use App\Models\Team;
use App\Services\Predictions\AlternativeProbabilityCalculator;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlternativeProbabilityCalculatorTest extends TestCase
{
    use RefreshDatabase;

    private AlternativeProbabilityCalculator $calculator;
    private Competition $comp;
    private Team $teamA;
    private Team $teamB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new AlternativeProbabilityCalculator();

        $this->comp = Competition::create([
            'slug' => 'premier-league',
            'name' => 'Premier League',
            'country' => 'England',
            'is_active' => true,
        ]);

        $this->teamA = Team::create(['name' => 'Arsenal FC', 'short_name' => 'Arsenal']);
        $this->teamB = Team::create(['name' => 'Chelsea FC', 'short_name' => 'Chelsea']);
    }

    public function test_calculates_sensible_probabilities_summing_to_one(): void
    {
        $match = FootballMatch::create([
            'competition_id' => $this->comp->id,
            'home_team_id' => $this->teamA->id,
            'away_team_id' => $this->teamB->id,
            'season' => 2026,
            'matchday' => 1,
            'kickoff_at' => CarbonImmutable::parse('2026-10-15 15:00:00'),
            'status' => 'scheduled',
        ]);

        $res = $this->calculator->calculate($match);

        $this->assertEquals('v1.0-elo', $res['system']);
        $this->assertGreaterThan(0.0, $res['p_home']);
        $this->assertGreaterThan(0.0, $res['p_draw']);
        $this->assertGreaterThan(0.0, $res['p_away']);
        $this->assertEqualsWithDelta(1.0, $res['p_home'] + $res['p_draw'] + $res['p_away'], 0.001);
        $this->assertContains($res['top_pick'], ['home', 'draw', 'away']);
    }

    public function test_mandatory_leakage_guard_future_matches_do_not_alter_ratings(): void
    {
        $asOf = CarbonImmutable::parse('2026-10-01 12:00:00');

        // Past match before asOf: Team A beat Team B 3-0
        FootballMatch::create([
            'competition_id' => $this->comp->id,
            'home_team_id' => $this->teamA->id,
            'away_team_id' => $this->teamB->id,
            'season' => 2026,
            'matchday' => 1,
            'kickoff_at' => CarbonImmutable::parse('2026-09-20 15:00:00'),
            'status' => 'finished',
            'home_goals' => 3,
            'away_goals' => 0,
        ]);

        $targetMatch = FootballMatch::create([
            'competition_id' => $this->comp->id,
            'home_team_id' => $this->teamA->id,
            'away_team_id' => $this->teamB->id,
            'season' => 2026,
            'matchday' => 2,
            'kickoff_at' => $asOf,
            'status' => 'scheduled',
        ]);

        $before = $this->calculator->calculate($targetMatch, $asOf);

        // Future match dated AFTER asOf: Team B beats Team A 5-0
        FootballMatch::create([
            'competition_id' => $this->comp->id,
            'home_team_id' => $this->teamB->id,
            'away_team_id' => $this->teamA->id,
            'season' => 2026,
            'matchday' => 3,
            'kickoff_at' => CarbonImmutable::parse('2026-10-10 15:00:00'),
            'status' => 'finished',
            'home_goals' => 5,
            'away_goals' => 0,
        ]);

        $after = $this->calculator->calculate($targetMatch, $asOf);

        // ZERO LEAKAGE ASSERTION: probabilities and ratings must be IDENTICAL
        $this->assertSame($before['p_home'], $after['p_home']);
        $this->assertSame($before['p_draw'], $after['p_draw']);
        $this->assertSame($before['p_away'], $after['p_away']);
        $this->assertSame($before['home_rating'], $after['home_rating']);
        $this->assertSame($before['away_rating'], $after['away_rating']);
    }

    public function test_consensus_calculation_unifies_or_detects_divergence(): void
    {
        // Agreeing scenario
        $consensusAgree = $this->calculator->calculateConsensus(
            0.55, 0.25, 0.20, // System 1 picks Home
            0.50, 0.28, 0.22, // System 2 picks Home
            'Arsenal', 'Chelsea'
        );

        $this->assertSame('home', $consensusAgree['consensus_pick']);
        $this->assertSame('High Agreement', $consensusAgree['confidence_level']);
        $this->assertStringContainsString('UNIFIED', $consensusAgree['status_badge']);

        // Diverging scenario
        $consensusDiverge = $this->calculator->calculateConsensus(
            0.48, 0.22, 0.30, // System 1 picks Home
            0.28, 0.24, 0.48, // System 2 picks Away
            'Arsenal', 'Chelsea'
        );

        $this->assertSame('Divergent Perspectives', $consensusDiverge['confidence_level']);
        $this->assertStringContainsString('DIVERGENCE', $consensusDiverge['status_badge']);
    }
}
