<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class ThemeSettingsTest extends TestCase
{
    public function test_theme_page_exposes_palette_management_and_local_persistence(): void
    {
        $response = $this->get(route('themes'));

        $response->assertOk()
            ->assertSee('Choose your theme')
            ->assertSee('Pitch Black')
            ->assertSee('Obsidian')
            ->assertSee('Midnight')
            ->assertSee('Light')
            ->assertSee("id: 'pitch-black'", false)
            ->assertSee("id: 'light'", false)
            ->assertSee('themeManager.getThemes().forEach((theme) =>', false)
            ->assertSee('selectButton.setAttribute(\'aria-pressed\'', false)
            ->assertSee('Create theme')
            ->assertSee("makeActionButton('Edit'", false)
            ->assertSee("makeActionButton('Delete'", false)
            ->assertSee('saveCustomThemes(nextCustomThemes)', false)
            ->assertSee('XPECT_CUSTOM_THEMES_STORAGE_KEY')
            ->assertSee("['accentPress', 'Accent pressed']", false)
            ->assertSee('href="' . route('themes') . '"', false);
    }
}
