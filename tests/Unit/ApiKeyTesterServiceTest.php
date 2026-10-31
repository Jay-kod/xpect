<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Http\Clients\LoggedHttpClient;
use App\Models\ApiCall;
use App\Services\Api\ApiKeyTesterService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ApiKeyTesterServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ApiKeyTesterService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(ApiKeyTesterService::class);
    }

    public function test_fails_immediately_when_no_key_provided_or_configured(): void
    {
        $result = $this->service->testKey('claude', '');
        $this->assertFalse($result->isValid);
        $this->assertSame(0, $result->statusCode);
        $this->assertStringContainsString('No Anthropic Claude API key', $result->message);

        $resultGemini = $this->service->testKey('gemini', '');
        $this->assertFalse($resultGemini->isValid);
        $this->assertSame(0, $resultGemini->statusCode);
        $this->assertStringContainsString('No Google Gemini API key', $resultGemini->message);
    }

    public function test_anthropic_claude_valid_key(): void
    {
        Http::fake([
            'https://api.anthropic.com/v1/messages' => Http::response([
                'id' => 'msg_123',
                'type' => 'message',
                'role' => 'assistant',
                'model' => 'claude-3-5-haiku-20241022',
                'content' => [['type' => 'text', 'text' => 'pong']],
                'usage' => ['input_tokens' => 5, 'output_tokens' => 2],
            ], 200),
        ]);

        $result = $this->service->testKey('claude', 'sk-ant-test-valid-key-999');

        $this->assertTrue($result->isValid);
        $this->assertSame(200, $result->statusCode);
        $this->assertStringContainsString('active and connected', $result->message);

        // Verify call saved in api_calls with header redacted
        $call = ApiCall::where('source', 'anthropic')->latest('id')->first();
        $this->assertNotNull($call);
        $this->assertSame(200, $call->response_status);
        $this->assertStringNotContainsString('sk-ant-test-valid-key-999', json_encode($call->request_headers));
    }

    public function test_anthropic_claude_invalid_key(): void
    {
        Http::fake([
            'https://api.anthropic.com/v1/messages' => Http::response([
                'type' => 'error',
                'error' => [
                    'type' => 'authentication_error',
                    'message' => 'invalid x-api-key',
                ],
            ], 401),
        ]);

        $result = $this->service->testKey('claude', 'sk-ant-bogus-key');

        $this->assertFalse($result->isValid);
        $this->assertSame(401, $result->statusCode);
        $this->assertStringContainsString('invalid x-api-key', $result->message);
    }

    public function test_openai_chatgpt_valid_key(): void
    {
        Http::fake([
            'https://api.openai.com/v1/chat/completions' => Http::response([
                'id' => 'chatcmpl-123',
                'model' => 'gpt-4o-mini',
                'choices' => [
                    ['message' => ['role' => 'assistant', 'content' => 'pong']],
                ],
                'usage' => ['prompt_tokens' => 4, 'completion_tokens' => 1],
            ], 200),
        ]);

        $result = $this->service->testKey('chatgpt', 'sk-proj-valid-key-123');

        $this->assertTrue($result->isValid);
        $this->assertSame(200, $result->statusCode);
        $this->assertStringContainsString('active and connected', $result->message);

        $call = ApiCall::where('source', 'openai')->latest('id')->first();
        $this->assertNotNull($call);
        $this->assertStringNotContainsString('sk-proj-valid-key-123', json_encode($call->request_headers));
    }

    public function test_openai_chatgpt_invalid_key(): void
    {
        Http::fake([
            'https://api.openai.com/v1/chat/completions' => Http::response([
                'error' => [
                    'message' => 'Incorrect API key provided: sk-bad...',
                    'type' => 'invalid_request_error',
                    'code' => 'invalid_api_key',
                ],
            ], 401),
        ]);

        $result = $this->service->testKey('chatgpt', 'sk-bad-key');

        $this->assertFalse($result->isValid);
        $this->assertSame(401, $result->statusCode);
        $this->assertStringContainsString('Incorrect API key', $result->message);
    }

    public function test_google_gemini_valid_key(): void
    {
        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [['text' => 'pong']],
                            'role' => 'model',
                        ],
                    ],
                ],
            ], 200),
        ]);

        $result = $this->service->testKey('gemini', 'AIzaSyTestSecret123');

        $this->assertTrue($result->isValid);
        $this->assertSame(200, $result->statusCode);
        $this->assertStringContainsString('active and connected', $result->message);

        $call = ApiCall::where('source', 'gemini')->latest('id')->first();
        $this->assertNotNull($call);
        $this->assertStringNotContainsString('AIzaSyTestSecret123', json_encode($call->request_headers));
    }

    public function test_google_gemini_invalid_key(): void
    {
        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'error' => [
                    'code' => 400,
                    'message' => 'API key not valid. Please pass a valid API key.',
                    'status' => 'INVALID_ARGUMENT',
                ],
            ], 400),
        ]);

        $result = $this->service->testKey('gemini', 'AIzaSyBadKey');

        $this->assertFalse($result->isValid);
        $this->assertSame(400, $result->statusCode);
        $this->assertStringContainsString('API key not valid', $result->message);
    }

    public function test_football_data_valid_key(): void
    {
        Http::fake([
            'https://api.football-data.org/v4/competitions/PL' => Http::response([
                'id' => 2021,
                'name' => 'Premier League',
                'code' => 'PL',
            ], 200, ['X-Requests-Available-Minute' => ['9']]),
        ]);

        $result = $this->service->testKey('football_data', 'fd-valid-token-1234');

        $this->assertTrue($result->isValid);
        $this->assertSame(200, $result->statusCode);
        $this->assertStringContainsString('active and connected', $result->message);
        $this->assertStringContainsString('9 requests left this minute', $result->details);

        $call = ApiCall::where('source', 'football_data')->latest('id')->first();
        $this->assertNotNull($call);
        $this->assertStringNotContainsString('fd-valid-token-1234', json_encode($call->request_headers));
    }

    public function test_football_data_invalid_key(): void
    {
        Http::fake([
            'https://api.football-data.org/v4/competitions/PL' => Http::response([
                'message' => 'Your API token is restricted or invalid.',
                'errorCode' => 403,
            ], 403),
        ]);

        $result = $this->service->testKey('football_data', 'fd-bad-token');

        $this->assertFalse($result->isValid);
        $this->assertSame(403, $result->statusCode);
        $this->assertStringContainsString('restricted or invalid', $result->message);
    }

    public function test_api_football_valid_key(): void
    {
        Http::fake([
            'https://v3.football.api-sports.io/status' => Http::response([
                'get' => 'status',
                'parameters' => [],
                'errors' => [],
                'results' => 1,
                'response' => [
                    'account' => [
                        'firstname' => 'John',
                        'email' => 'john@example.com',
                    ],
                    'subscription' => [
                        'plan' => 'Free',
                        'active' => true,
                    ],
                    'requests' => [
                        'current' => 12,
                        'limit_day' => 100,
                    ],
                ],
            ], 200),
        ]);

        $result = $this->service->testKey('api_football', 'apisp-valid-key-999');

        $this->assertTrue($result->isValid);
        $this->assertSame(200, $result->statusCode);
        $this->assertStringContainsString('API-Football key is active', $result->message);
        $this->assertStringContainsString('Plan: Free', $result->message);
        $this->assertStringContainsString('12/100 requests', $result->details);

        $call = ApiCall::where('source', 'api_football')->latest('id')->first();
        $this->assertNotNull($call);
        $this->assertStringNotContainsString('apisp-valid-key-999', json_encode($call->request_headers));
    }

    public function test_api_football_invalid_key_in_errors_envelope(): void
    {
        Http::fake([
            'https://v3.football.api-sports.io/status' => Http::response([
                'get' => 'status',
                'parameters' => [],
                'errors' => [
                    'token' => 'Error/Unauthorized: invalid API key',
                ],
                'results' => 0,
                'response' => [],
            ], 200),
        ]);

        $result = $this->service->testKey('api_football', 'apisp-invalid-key');

        $this->assertFalse($result->isValid);
        $this->assertStringContainsString('invalid API key', $result->message);
    }
}

