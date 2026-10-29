<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ApiCall;
use App\Models\Competition;
use App\Models\FootballMatch;
use App\Models\Prediction;
use App\Models\Preview;
use App\Models\Team;
use App\Services\Previews\PreviewGenerator;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PreviewGeneratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_generates_preview_via_logged_http_client_and_persists_audit_trail(): void
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

        $pred->setRelation('match', $match);

        $fakePreviewText = "Arsenal hold a 55.2% win probability against Chelsea (20.5%), with the draw at 24.3%. Expected goals stand at 1.85 to 0.95, pointing towards a 2-1 outcome.";

        Http::fake([
            'https://api.anthropic.com/v1/messages' => Http::response([
                'id' => 'msg_123',
                'type' => 'message',
                'role' => 'assistant',
                'content' => [
                    [
                        'type' => 'text',
                        'text' => $fakePreviewText,
                    ],
                ],
                'model' => 'claude-3-5-sonnet-20241022',
                'usage' => [
                    'input_tokens' => 312,
                    'output_tokens' => 88,
                ],
            ], 200),
        ]);

        config(['services.anthropic.key' => 'sk-ant-test-secret-key-12345']);

        $generator = app(PreviewGenerator::class);
        $preview = $generator->generate($pred);

        $this->assertInstanceOf(Preview::class, $preview);
        $this->assertSame($fakePreviewText, $preview->text);
        $this->assertFalse($preview->flagged);
        $this->assertSame('v1.0.0', $preview->prompt_version);

        // Verify api_calls row was created with secret redacted
        $apiCall = ApiCall::find($preview->api_call_id);
        $this->assertNotNull($apiCall);
        $this->assertSame('anthropic', $apiCall->source);
        $this->assertSame(312, $apiCall->input_tokens);
        $this->assertSame(88, $apiCall->output_tokens);

        // Key must be redacted
        $this->assertStringNotContainsString('sk-ant-test-secret-key-12345', json_encode($apiCall->request_headers));
        $this->assertSame('[REDACTED]', $apiCall->request_headers['x-api-key'] ?? null);
    }
}

