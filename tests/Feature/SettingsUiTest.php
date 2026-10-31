<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SettingsUiTest extends TestCase
{
    use RefreshDatabase;

    private ?string $originalEnv = null;

    protected function setUp(): void
    {
        parent::setUp();
        if (file_exists(base_path('.env'))) {
            $this->originalEnv = (string) file_get_contents(base_path('.env'));
        }
    }

    protected function tearDown(): void
    {
        if ($this->originalEnv !== null && file_exists(base_path('.env'))) {
            file_put_contents(base_path('.env'), $this->originalEnv);
        }
        parent::tearDown();
    }

    public function test_settings_page_renders_keys_and_system_stats(): void
    {
        $response = $this->get('/settings');

        $response->assertStatus(200);
        $response->assertSee('Settings');
        $response->assertSee('football-data.org API Key');
        $response->assertSee('Anthropic Claude API Key');
        $response->assertSee('Local System Operations');

        // Direct API portal links
        $response->assertSee('https://console.anthropic.com/settings/keys');
        $response->assertSee('https://platform.openai.com/api-keys');
        $response->assertSee('https://aistudio.google.com/app/apikey');
        $response->assertSee('https://www.football-data.org/client/register');
        $response->assertSee('https://dashboard.api-football.com/register');
    }

    public function test_settings_page_renders_multi_llm_providers_and_dropdowns(): void
    {
        $response = $this->get('/settings');

        $response->assertStatus(200);
        $response->assertSee('Active AI Provider');
        $response->assertSee('Anthropic Claude');
        $response->assertSee('OpenAI ChatGPT');
        $response->assertSee('Google Gemini');
        $response->assertSee('claude-3-5-sonnet-latest');
        $response->assertSee('OpenAI ChatGPT API Key');
        $response->assertSee('Google Gemini API Key');
    }

    public function test_settings_can_update_ai_provider_and_keys(): void
    {
        $response = $this->post(route('settings.keys'), [
            'ai_provider' => 'chatgpt',
            'ai_model' => 'gpt-4o',
            'openai_key' => 'sk-proj-test-secret-key-12345',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_settings_can_trigger_system_actions(): void
    {
        $response = $this->post(route('settings.action'), [
            'action' => 'app_backup',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_settings_page_renders_test_key_buttons_and_feedback_elements(): void
    {
        $response = $this->get('/settings');

        $response->assertStatus(200);
        $response->assertSee('Test Key');
        $response->assertSee('btn-test-key');
        $response->assertSee('test-feedback-claude');
        $response->assertSee('test-feedback-chatgpt');
        $response->assertSee('test-feedback-gemini');
        $response->assertSee('test-feedback-football_data');
        $response->assertSee('test-feedback-api_football');
    }

    public function test_settings_test_key_endpoint_via_json(): void
    {
        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    ['content' => ['parts' => [['text' => 'pong']], 'role' => 'model']],
                ],
            ], 200),
        ]);

        $response = $this->postJson(route('settings.test-key'), [
            'provider' => 'gemini',
            'key' => 'AIzaSyTestValid123',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'provider' => 'gemini',
            'is_valid' => true,
            'status_code' => 200,
        ]);
        $response->assertJsonFragment(['is_valid' => true]);
    }

    public function test_settings_test_key_endpoint_via_form_redirect(): void
    {
        Http::fake([
            'https://api.openai.com/v1/chat/completions' => Http::response([
                'id' => 'chatcmpl-test',
                'model' => 'gpt-4o-mini',
                'choices' => [['message' => ['role' => 'assistant', 'content' => 'pong']]],
            ], 200),
        ]);

        $response = $this->post(route('settings.test-key'), [
            'provider' => 'chatgpt',
            'key' => 'sk-proj-test-1234',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_artisan_test_key_command(): void
    {
        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    ['content' => ['parts' => [['text' => 'pong']], 'role' => 'model']],
                ],
            ], 200),
        ]);

        $this->artisan('test:key', ['provider' => 'gemini', '--key' => 'AIzaSyTestCliKey'])
            ->expectsOutputToContain('ACTIVE')
            ->assertExitCode(0);
    }
}
