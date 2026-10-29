<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Competition;
use App\Models\FootballMatch;
use App\Models\Prediction;
use App\Models\Team;
use App\Services\Previews\PromptBuilder;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromptBuilderTest extends TestCase
{
    use RefreshDatabase;

    protected PromptBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->builder = new PromptBuilder();
    }

    public function test_builds_structured_payload_strictly_from_prediction_data(): void
    {
        $comp = Competition::create([
            'slug' => 'premier-league',
            'name' => 'Premier League',
            'country' => 'England',
            'is_active' => true,
        ]);

        $home = Team::create(['name' => 'Arsenal FC', 'short_name' => 'Arsenal']);
        $away = Team::create(['name' => 'Chelsea FC', 'short_name' => 'Chelsea']);

        $match = FootballMatch::create([
            'competition_id' => $comp->id,
            'home_team_id' => $home->id,
            'away_team_id' => $away->id,
            'season' => 2024,
            'matchday' => 10,
            'kickoff_at' => CarbonImmutable::parse('2024-11-02 15:00:00'),
            'status' => 'scheduled',
        ]);

        $pred = Prediction::create([
            'match_id' => $match->id,
            'model_version' => 'v1.0.0-poisson',
            'is_backtest' => false,
            'as_of' => CarbonImmutable::parse('2024-11-02 12:00:00'),
            'p_home' => 0.5520,
            'p_draw' => 0.2430,
            'p_away' => 0.2050,
            'exp_home_goals' => 1.85,
            'exp_away_goals' => 0.95,
            'top_scorelines' => [
                ['home' => 2, 'away' => 1, 'prob' => 0.12],
                ['home' => 1, 'away' => 0, 'prob' => 0.11],
            ],
            'features' => [
                'home' => ['attack' => 1.25, 'defence' => 0.85],
                'away' => ['attack' => 0.90, 'defence' => 1.15],
            ],
        ]);

        $pred->setRelation('match', $match);

        $payload = $this->builder->buildPayload($pred);

        $this->assertSame(PromptBuilder::PROMPT_VERSION, $payload['prompt_version']);
        $this->assertNotEmpty($payload['system']);
        $this->assertStringContainsString('STRICT INSTRUCTIONS', $payload['system']);

        $json = $payload['input_json'];
        $this->assertSame('Arsenal FC', $json['teams']['home']['name']);
        $this->assertSame('Chelsea FC', $json['teams']['away']['name']);
        $this->assertEquals(55.2, $json['model_output']['probabilities']['home_win_pct']);
        $this->assertEquals(24.3, $json['model_output']['probabilities']['draw_pct']);
        $this->assertEquals(20.5, $json['model_output']['probabilities']['away_win_pct']);
        $this->assertEquals(1.85, $json['model_output']['expected_goals']['home']);
        $this->assertEquals(0.95, $json['model_output']['expected_goals']['away']);
        $this->assertEquals(2.80, $json['model_output']['expected_goals']['total']);
    }
}

