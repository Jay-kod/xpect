<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Competition;
use App\Models\FootballMatch;
use App\Models\MotmEvaluation;
use App\Models\Team;
use App\Services\Matches\MatchHistoryService;
use App\Services\Motm\MotmEvaluator;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MatchHistoryServiceTest extends TestCase
{
    use RefreshDatabase;

    private MatchHistoryService $service;
    private Competition $comp;
    private Team $teamA;
    private Team $teamB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(MatchHistoryService::class);

        $this->comp = Competition::create([
            'slug' => 'premier-league',
            'name' => 'Premier League',
            'country' => 'England',
            'is_active' => true,
        ]);

        $this->teamA = Team::create(['name' => 'Arsenal FC', 'short_name' => 'Arsenal']);
        $this->teamB = Team::create(['name' => 'Liverpool FC', 'short_name' => 'Liverpool']);
    }

    public function test_head_to_head_calculates_correct_records_and_stats(): void
    {
        // Past Match 1: Arsenal 3 - 1 Liverpool (Arsenal Won)
        FootballMatch::create([
            'competition_id' => $this->comp->id,
            'home_team_id' => $this->teamA->id,
            'away_team_id' => $this->teamB->id,
            'season' => 2023,
            'matchday' => 23,
            'kickoff_at' => CarbonImmutable::parse('2024-02-04 16:30:00'),
            'status' => 'finished',
            'home_goals' => 3,
            'away_goals' => 1,
            'ht_home_goals' => 1,
            'ht_away_goals' => 1,
        ]);

        // Past Match 2: Liverpool 1 - 1 Arsenal (Draw)
        FootballMatch::create([
            'competition_id' => $this->comp->id,
            'home_team_id' => $this->teamB->id,
            'away_team_id' => $this->teamA->id,
            'season' => 2023,
            'matchday' => 18,
            'kickoff_at' => CarbonImmutable::parse('2023-12-23 17:30:00'),
            'status' => 'finished',
            'home_goals' => 1,
            'away_goals' => 1,
            'ht_home_goals' => 1,
            'ht_away_goals' => 1,
        ]);

        // Past Match 3: Arsenal 0 - 2 Liverpool (Liverpool Won)
        FootballMatch::create([
            'competition_id' => $this->comp->id,
            'home_team_id' => $this->teamA->id,
            'away_team_id' => $this->teamB->id,
            'season' => 2022,
            'matchday' => 27,
            'kickoff_at' => CarbonImmutable::parse('2023-01-07 16:30:00'),
            'status' => 'finished',
            'home_goals' => 0,
            'away_goals' => 2,
            'ht_home_goals' => 0,
            'ht_away_goals' => 0,
        ]);

        // Target Upcoming Match: Arsenal (Home) vs Liverpool (Away)
        $currentMatch = FootballMatch::create([
            'competition_id' => $this->comp->id,
            'home_team_id' => $this->teamA->id,
            'away_team_id' => $this->teamB->id,
            'season' => 2026,
            'matchday' => 9,
            'kickoff_at' => CarbonImmutable::parse('2026-11-01 16:30:00'),
            'status' => 'scheduled',
        ]);

        $h2h = $this->service->getHeadToHead($currentMatch);

        $this->assertEquals(3, $h2h['total_played']);
        $this->assertEquals(1, $h2h['home_wins']); // Arsenal won Match 1
        $this->assertEquals(1, $h2h['draws']);     // Match 2 was a draw
        $this->assertEquals(1, $h2h['away_wins']); // Liverpool won Match 3
        $this->assertEquals(4, $h2h['home_goals_total']); // Arsenal goals: 3 + 1 + 0 = 4
        $this->assertEquals(4, $h2h['away_goals_total']); // Liverpool goals: 1 + 1 + 2 = 4
        $this->assertEquals(2.7, $h2h['avg_goals']);     // (4 + 4) / 3 = 2.66 -> 2.7
        $this->assertEquals(2, $h2h['btts_count']);
        $this->assertEquals(1, $h2h['over_25_count']);
        $this->assertEquals(0, $h2h['clean_sheets_home']);
        $this->assertEquals(1, $h2h['clean_sheets_away']); // Liverpool kept 1 clean sheet in 0-2
    }

    public function test_mandatory_leakage_guard_future_h2h_matches_do_not_leak(): void
    {
        // Past Match (Before cutoff)
        FootballMatch::create([
            'competition_id' => $this->comp->id,
            'home_team_id' => $this->teamA->id,
            'away_team_id' => $this->teamB->id,
            'season' => 2023,
            'kickoff_at' => CarbonImmutable::parse('2023-12-01 15:00:00'),
            'status' => 'finished',
            'home_goals' => 2,
            'away_goals' => 0,
        ]);

        // Current target match on 2024-01-01
        $currentMatch = FootballMatch::create([
            'competition_id' => $this->comp->id,
            'home_team_id' => $this->teamA->id,
            'away_team_id' => $this->teamB->id,
            'season' => 2024,
            'kickoff_at' => CarbonImmutable::parse('2024-01-01 15:00:00'),
            'status' => 'scheduled',
        ]);

        // Future Match (After cutoff) - MUST NEVER BE INCLUDED
        FootballMatch::create([
            'competition_id' => $this->comp->id,
            'home_team_id' => $this->teamB->id,
            'away_team_id' => $this->teamA->id,
            'season' => 2024,
            'kickoff_at' => CarbonImmutable::parse('2024-05-01 15:00:00'),
            'status' => 'finished',
            'home_goals' => 5,
            'away_goals' => 5,
        ]);

        $h2h = $this->service->getHeadToHead($currentMatch);

        $this->assertEquals(1, $h2h['total_played']);
        $this->assertEquals(1, $h2h['home_wins']);
        $this->assertEquals(2, $h2h['home_goals_total']);
        $this->assertEquals(0, $h2h['away_goals_total']);
    }

    public function test_performance_details_computes_motm_team_ratings_and_squad(): void
    {
        $match = FootballMatch::create([
            'competition_id' => $this->comp->id,
            'home_team_id' => $this->teamA->id,
            'away_team_id' => $this->teamB->id,
            'season' => 2024,
            'matchday' => 10,
            'kickoff_at' => CarbonImmutable::parse('2024-10-20 15:00:00'),
            'status' => 'finished',
            'home_goals' => 2,
            'away_goals' => 1,
            'ht_home_goals' => 1,
            'ht_away_goals' => 0,
        ]);

        $perf = $this->service->getPerformanceDetails($match, autoEvaluate: true);

        $this->assertTrue($perf['has_evaluations']);
        $this->assertNotNull($perf['motm']);
        $this->assertInstanceOf(MotmEvaluation::class, $perf['motm']);
        $this->assertTrue($perf['motm']->is_motm);

        $this->assertCount(11, $perf['home_players']);
        $this->assertCount(11, $perf['away_players']);

        $this->assertNotNull($perf['home_team_rating']);
        $this->assertNotNull($perf['away_team_rating']);
        $this->assertGreaterThan(5.0, $perf['home_team_rating']);
        $this->assertGreaterThan(5.0, $perf['away_team_rating']);

        $this->assertNotNull($perf['home_units']['attack']);
        $this->assertNotNull($perf['home_units']['midfield']);
        $this->assertNotNull($perf['home_units']['defense']);
    }
}
