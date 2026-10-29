<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ApiCall;
use App\Models\Competition;
use App\Models\FootballMatch;
use App\Models\Prediction;
use App\Models\Preview;
use App\Models\Team;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiReasoningStudioTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $comp = Competition::create([
            'slug' => 'premier-league',
            'name' => 'Premier League',
            'country' => 'England',
            'is_active' => true,
        ]);

        $home = Team::create(['name' => 'Manchester City FC', 'short_name' => 'Man City']);
        $away = Team::create(['name' => 'Arsenal FC', 'short_name' => 'Arsenal']);

        $match = FootballMatch::create([
            'competition_id' => $comp->id,
            'home_team_id' => $home->id,
            'away_team_id' => $away->id,
            'season' => 2026,
            'matchday' => 10,
            'kickoff_at' => CarbonImmutable::now()->addDays(2),
            'status' => 'scheduled',
        ]);

        Prediction::create([
            'match_id' => $match->id,
            'model_version' => 'v1.2.1-h2h',
            'is_backtest' => false,
            'as_of' => CarbonImmutable::now()->subHour(),
            'p_home' => 0.5230,
            'p_draw' => 0.2450,
            'p_away' => 0.2320,
            'exp_home_goals' => 1.85,
            'exp_away_goals' => 1.05,
            'top_scorelines' => [
                ['score' => '1-0', 'home' => 1, 'away' => 0, 'prob' => 0.124],
                ['score' => '2-1', 'home' => 2, 'away' => 1, 'prob' => 0.098],
                ['score' => '1-1', 'home' => 1, 'away' => 1, 'prob' => 0.095],
                ['score' => '2-0', 'home' => 2, 'away' => 0, 'prob' => 0.088],
                ['score' => '0-0', 'home' => 0, 'away' => 0, 'prob' => 0.065],
            ],
            'features' => [
                'home' => [
                    'home_xg_attack' => 1.25,
                    'home_attack' => 1.20,
                    'home_xg_defence' => 0.85,
                    'home_defence' => 0.90,
                    'league_avg_home_goals' => 1.55,
                    'league_avg_away_goals' => 1.25,
                ],
                'away' => [
                    'away_xg_attack' => 1.10,
                    'away_attack' => 1.05,
                    'away_xg_defence' => 0.95,
                    'away_defence' => 1.00,
                ],
                'h2h_modifier' => 1.02,
            ],
        ]);
    }

    public function test_ai_reasoning_studio_renders_successfully(): void
    {
        $response = $this->get('/ai-reasoning');

        $response->assertStatus(200);
        $response->assertSee('AI Reasoning Studio &amp; Permutation Explainer', false);
        $response->assertSee('Engine 1: Pure Mathematical Model');
        $response->assertSee('Engine 2: Grounded AI Reasoning');
        $response->assertSee('Scoreline Probability Heatmap (0..5 &times; 0..5)', false);
        $response->assertSee('Structured Payload Passed to LLM');
        $response->assertSee('Anti-Hallucination Guard Tester');
        $response->assertSee('Persistent Reasoning History');
    }

    public function test_ai_reasoning_studio_renders_with_selected_match(): void
    {
        $match = FootballMatch::first();

        $response = $this->get('/ai-reasoning?match_id=' . $match->id);

        $response->assertStatus(200);
        $response->assertSee('Man City');
        $response->assertSee('Arsenal');
        $response->assertSee('52.3% H');
        $response->assertSee('1.85 &ndash; 1.05', false);
    }

    public function test_simulate_action_generates_simulation_and_saves_history(): void
    {
        $prediction = Prediction::first();

        $response = $this->post('/ai-reasoning/simulate', [
            'prediction_id' => $prediction->id,
            'mode' => 'simulation',
        ]);

        $response->assertRedirect('/ai-reasoning?match_id=' . $prediction->match_id);
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('previews', [
            'prediction_id' => $prediction->id,
            'flagged' => false,
        ]);

        $preview = Preview::where('prediction_id', $prediction->id)->latest()->first();
        $this->assertNotNull($preview);
        $this->assertNotNull($preview->api_call_id);

        $this->assertDatabaseHas('api_calls', [
            'id' => $preview->api_call_id,
            'source' => 'anthropic',
            'triggered_by' => 'ai-reasoning:simulate',
            'response_status' => 200,
        ]);
    }

    public function test_check_guard_api_verifies_allowed_and_flags_disallowed_numbers(): void
    {
        $prediction = Prediction::first();

        // 1. Text with verified allowed numbers (52.3%, 1.85, 1-0)
        $cleanResponse = $this->postJson('/ai-reasoning/check-guard', [
            'prediction_id' => $prediction->id,
            'text' => 'Manchester City have a 52.3% home win probability with expected goals of 1.85 and 1-0 as a top scoreline.',
        ]);

        $cleanResponse->assertStatus(200);
        $cleanResponse->assertJson([
            'flagged' => false,
            'disallowed_numbers' => [],
        ]);

        // 2. Text with fabricated/hallucinated numbers (e.g. 99.9%, 77 goals)
        $hallucinatedResponse = $this->postJson('/ai-reasoning/check-guard', [
            'prediction_id' => $prediction->id,
            'text' => 'Manchester City have a 99.9% chance of winning after scoring 77 goals in past seasons.',
        ]);

        $hallucinatedResponse->assertStatus(200);
        $hallucinatedResponse->assertJson([
            'flagged' => true,
        ]);
        $this->assertContains('99.9', $hallucinatedResponse->json('disallowed_numbers'));
        $this->assertContains('77', $hallucinatedResponse->json('disallowed_numbers'));
    }
}
