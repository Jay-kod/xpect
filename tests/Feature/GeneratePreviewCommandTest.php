<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Competition;
use App\Models\FootballMatch;
use App\Models\Prediction;
use App\Models\Team;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GeneratePreviewCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_generates_preview_and_prints_audit_info(): void
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
            'top_scorelines' => [['home' => 2, 'away' => 1, 'prob' => 0.12]],
            'features' => [],
        ]);

        Http::fake([
            'https://api.anthropic.com/v1/messages' => Http::response([
                'id' => 'msg_abc',
                'type' => 'message',
                'role' => 'assistant',
                'content' => [
                    [
                        'type' => 'text',
                        'text' => 'Arsenal (55.2%) are favoured against Chelsea (20.5%). Expected goals stand at 1.85 to 0.95 with 2-1 the top scoreline.',
                    ],
                ],
                'model' => 'claude-3-5-sonnet-20241022',
                'usage' => [
                    'input_tokens' => 280,
                    'output_tokens' => 65,
                ],
            ], 200),
        ]);

        config(['services.anthropic.key' => 'sk-ant-test-key']);

        $this->artisan("preview:generate {$pred->id}")
            ->expectsOutputToContain('XPECT AI PREVIEW GENERATOR')
            ->expectsOutputToContain('Arsenal FC vs Chelsea FC')
            ->expectsOutputToContain('Number-Check Guard:  PASSED')
            ->assertExitCode(0);

        $this->assertDatabaseHas('previews', [
            'prediction_id' => $pred->id,
            'flagged' => 0,
        ]);
    }
}

