<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
