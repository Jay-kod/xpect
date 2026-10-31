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

class MultiModelPreviewTest extends TestCase
{
    use RefreshDatabase;

    private Prediction $prediction;

    protected function setUp(): void
    {
        parent::setUp();

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
            'season' => 2026,
            'matchday' => 12,
            'kickoff_at' => CarbonImmutable::parse('2026-11-21 15:00:00'),
            'status' => 'scheduled',
        ]);

        $this->prediction = Prediction::create([
            'match_id' => $match->id,
            'model_version' => 'v1.2.1-h2h',
            'is_backtest' => false,
            'as_of' => CarbonImmutable::parse('2026-11-21 12:00:00'),
            'p_home' => 0.5400,
            'p_draw' => 0.2500,
            'p_away' => 0.2100,
            'exp_home_goals' => 1.80,
            'exp_away_goals' => 0.90,
            'top_scorelines' => [['home' => 2, 'away' => 1, 'prob' => 0.12]],
            'features' => [],
        ]);

        $this->prediction->setRelation('match', $match);
    }

    public function test_generates_preview_via_openai_chatgpt(): void
    {
        $fakeText = "Arsenal hold a 54% win probability against Chelsea (21%), with the draw projected at 25%. Expected goals stand at 1.80 to 0.90, pointing towards a 2-1 outcome.";

        Http::fake([
            'https://api.openai.com/v1/chat/completions' => Http::response([
                'id' => 'chatcmpl_123',
                'object' => 'chat.completion',
                'created' => time(),
                'model' => 'gpt-4o',
                'choices' => [
                    [
                        'index' => 0,
                        'message' => [
                            'role' => 'assistant',
                            'content' => $fakeText,
                        ],
                        'finish_reason' => 'stop',
                    ],
                ],
                'usage' => [
                    'prompt_tokens' => 260,
                    'completion_tokens' => 55,
                    'total_tokens' => 315,
                ],
            ], 200),
        ]);

        config(['services.openai.key' => 'sk-proj-test-key-openai']);

        $generator = app(PreviewGenerator::class);
        $preview = $generator->generate($this->prediction, modelOverride: 'gpt-4o', providerOverride: 'chatgpt');

        $this->assertInstanceOf(Preview::class, $preview);
        $this->assertSame($fakeText, $preview->text);
        $this->assertSame('gpt-4o', $preview->model);
        $this->assertFalse($preview->flagged);

        // Verify api_calls entry created
        $call = ApiCall::find($preview->api_call_id);
        $this->assertNotNull($call);
        $this->assertSame('openai', $call->source);
        $this->assertSame(260, $call->input_tokens);
        $this->assertSame(55, $call->output_tokens);
        $this->assertStringNotContainsString('sk-proj-test-key-openai', json_encode($call->request_headers));
    }

    public function test_generates_preview_via_google_gemini(): void
    {
        $fakeText = "Arsenal hold a 54% win probability against Chelsea (21%), with the draw projected at 25%. Expected goals stand at 1.80 to 0.90, pointing towards a 2-1 outcome.";

        Http::fake([
            'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-pro:generateContent*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => $fakeText],
                            ],
                            'role' => 'model',
                        ],
                        'finishReason' => 'STOP',
                    ],
                ],
                'usageMetadata' => [
                    'promptTokenCount' => 240,
                    'candidatesTokenCount' => 50,
                    'totalTokenCount' => 290,
                ],
            ], 200),
        ]);

        config(['services.gemini.key' => 'AIzaSyTestGeminiKey123']);

        $generator = app(PreviewGenerator::class);
        $preview = $generator->generate($this->prediction, modelOverride: 'gemini-1.5-pro', providerOverride: 'gemini');

        $this->assertInstanceOf(Preview::class, $preview);
        $this->assertSame($fakeText, $preview->text);
        $this->assertSame('gemini-1.5-pro', $preview->model);
        $this->assertFalse($preview->flagged);

        // Verify api_calls entry created
        $call = ApiCall::find($preview->api_call_id);
        $this->assertNotNull($call);
        $this->assertSame('gemini', $call->source);
        $this->assertSame(240, $call->input_tokens);
        $this->assertSame(50, $call->output_tokens);
        $this->assertStringNotContainsString('AIzaSyTestGeminiKey123', json_encode($call->request_headers));
    }
}
