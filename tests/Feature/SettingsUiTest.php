<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_page_renders_keys_and_system_stats(): void
    {
        $response = $this->get('/settings');

        $response->assertStatus(200);
        $response->assertSee('Settings');
        $response->assertSee('football-data.org API Key');
        $response->assertSee('Anthropic Claude API Key');
        $response->assertSee('Local System Operations');
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

