<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Competition;
use App\Models\FootballMatch;
use App\Models\MatchLineup;
use App\Models\MotmEvaluation;
use App\Models\Team;
use App\Services\Motm\MotmEvaluator;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MotmTest extends TestCase
{
    use RefreshDatabase;

    protected FootballMatch $match;
    protected Team $homeTeam;
    protected Team $awayTeam;
    protected bool $fakeResearchVerified = true;

    protected function setUp(): void
    {
        parent::setUp();

        $comp = Competition::create([
            'slug' => 'premier-league',
            'name' => 'Premier League',
            'country' => 'England',
            'is_active' => true,
        ]);

        $this->homeTeam = Team::create(['name' => 'Liverpool FC', 'short_name' => 'Liverpool']);
        $this->awayTeam = Team::create(['name' => 'Everton FC', 'short_name' => 'Everton']);

        $this->match = FootballMatch::create([
            'competition_id' => $comp->id,
            'home_team_id' => $this->homeTeam->id,
            'away_team_id' => $this->awayTeam->id,
            'season' => 2026,
            'matchday' => 8,
            'kickoff_at' => CarbonImmutable::now()->subDays(3),
            'status' => 'finished',
            'home_goals' => 2,
            'away_goals' => 0,
        ]);

        config(['services.gemini.key' => 'test-key']);
        Http::fake(function ($request) {
            $payload = json_decode($request->body(), true);
            $prompt = json_encode($payload['contents'] ?? []);
            if (!$this->fakeResearchVerified) {
                $result = ['verified' => false, 'sources' => []];
            } elseif (str_contains($prompt, 'Aston Villa')) {
                $result = [
                    'verified' => true,
                    'player_name' => 'Verified Villa Player',
                    'team_name' => 'Aston Villa',
                    'reason' => 'The official report named the player for a decisive performance.',
                    'sources' => [['title' => 'Official report', 'url' => 'https://example.test/villa-report']],
                ];
            } else {
                $result = [
                    'verified' => true,
                    'player_name' => 'Verified Player',
                    'team_name' => 'Liverpool',
                    'reason' => 'The official match report named the player for a decisive performance.',
                    'sources' => [[
                        'title' => 'Official match report',
                        'url' => 'https://example.test/match-report',
                        'publisher' => 'Official source',
                    ]],
                    'search_queries' => ['Liverpool Everton Man of the Match'],
                ];
            }

            return Http::response([
                'candidates' => [[
                    'content' => ['parts' => [[
                        'text' => json_encode($result),
                    ]]],
                    'groundingMetadata' => [
                        'groundingChunks' => [[
                            'web' => [
                                'uri' => $result['sources'][0]['url'] ?? 'https://example.test/match-report',
                                'title' => $result['sources'][0]['title'] ?? 'Official report',
                            ],
                        ]],
                    ],
                ]],
            ], 200);
        });
    }

    public function test_motm_page_renders_successfully(): void
    {
        $response = $this->get('/motm');

        $response->assertStatus(200);
        $response->assertSee('Man of the Match &amp; Verified Research Hub', false);
        $response->assertSee('Only Explicitly Verified Awards');
        $response->assertSee('Top Players &bull; Most MOTM Decisions', false);
        $response->assertSee('Top Clubs &bull; Total MOTM Tally', false);
        $response->assertSee('Confirmed Match-by-Match Man of the Match Feed');
    }

    public function test_motm_evaluator_guarantees_strictly_one_winner_with_confirmed_player(): void
    {
        $evaluator = app(MotmEvaluator::class);
        $evaluations = $evaluator->evaluateMatch($this->match);

        $this->assertNotEmpty($evaluations);
        $this->assertCount(22, $evaluations); // 11 home + 11 away players

        // STRICTLY EXACTLY ONE MOTM WINNER
        $motmWinners = $evaluations->where('is_motm', true);
        $this->assertCount(1, $motmWinners);

        $winner = $motmWinners->first();
        // Asserts real authentic confirmed player name (e.g. Mohamed Salah or Liverpool star) rather than "Liverpool #9"
        $this->assertNotEmpty($winner->player_name);
        $this->assertStringNotContainsString('Liverpool #', $winner->player_name);

        $this->assertGreaterThanOrEqual(6.0, $winner->rating);
        $this->assertLessThanOrEqual(10.0, $winner->rating);

        // Asserts rich tactical explanation of WHY he won
        $this->assertNotEmpty($winner->performance_summary);
        $this->assertTrue(
            str_contains($winner->performance_summary, 'Man of the Match') ||
            str_contains($winner->performance_summary, 'rating')
        );

        // Verify clean sheet defenders receive higher rating than losing defenders
        $homeDefender = $evaluations->where('team_id', $this->homeTeam->id)->where('position', 'D')->first();
        $awayDefender = $evaluations->where('team_id', $this->awayTeam->id)->where('position', 'D')->first();

        $this->assertNotNull($homeDefender);
        $this->assertNotNull($awayDefender);
        $this->assertGreaterThan($awayDefender->rating, $homeDefender->rating);
    }

    public function test_motm_evaluator_respects_lineups_table_when_present(): void
    {
        MatchLineup::create([
            'match_id' => $this->match->id,
            'team_id' => $this->homeTeam->id,
            'formation' => '4-3-3',
            'starting_xi' => [
                ['player' => ['name' => 'Mohamed Salah', 'number' => 11, 'pos' => 'F']],
                ['player' => ['name' => 'Virgil van Dijk', 'number' => 4, 'pos' => 'D']],
            ],
            'confirmed' => true,
        ]);

        $evaluator = app(MotmEvaluator::class);
        $evaluations = $evaluator->evaluateMatch($this->match, force: true);

        $salah = $evaluations->firstWhere('player_name', 'Mohamed Salah');
        $this->assertNotNull($salah);
        $this->assertEquals(11, $salah->player_number);
        $this->assertEquals('F', $salah->position);
    }

    public function test_evaluate_route_evaluates_match_and_redirects(): void
    {
        $response = $this->post(route('motm.evaluate', $this->match->id));

        $response->assertRedirect('/motm?match_id=' . $this->match->id);
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('motm_evaluations', [
            'match_id' => $this->match->id,
            'is_motm' => true,
        ]);

        // Assert strictly 1 MOTM in database
        $count = MotmEvaluation::where('match_id', $this->match->id)->where('is_motm', true)->count();
        $this->assertEquals(1, $count);
    }

    public function test_verified_research_does_not_create_award_without_explicit_source(): void
    {
        $this->fakeResearchVerified = false;

        $response = $this->post(route('motm.evaluate', $this->match->id));

        $response->assertSessionHas('success', 'No source explicitly named a Man of the Match for this fixture.');
        $this->assertDatabaseMissing('motm_evaluations', [
            'match_id' => $this->match->id,
            'verification_status' => 'verified',
        ]);
    }

    public function test_batch_evaluate_route_processes_unevaluated_matches(): void
    {
        $comp = Competition::first();
        $t1 = Team::create(['name' => 'Aston Villa FC', 'short_name' => 'Aston Villa']);
        $t2 = Team::create(['name' => 'Fulham FC', 'short_name' => 'Fulham']);

        $match2 = FootballMatch::create([
            'competition_id' => $comp->id,
            'home_team_id' => $t1->id,
            'away_team_id' => $t2->id,
            'season' => 2026,
            'matchday' => 8,
            'kickoff_at' => CarbonImmutable::now()->subDays(2),
            'status' => 'finished',
            'home_goals' => 1,
            'away_goals' => 1,
        ]);

        $response = $this->post(route('motm.batch'));

        $response->assertRedirect('/motm');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('motm_evaluations', [
            'match_id' => $match2->id,
            'is_motm' => true,
        ]);

        // Verify exactly one MOTM in match2
        $count2 = MotmEvaluation::where('match_id', $match2->id)->where('is_motm', true)->count();
        $this->assertEquals(1, $count2);
    }
}
