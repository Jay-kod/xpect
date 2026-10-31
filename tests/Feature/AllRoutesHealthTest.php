<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Competition;
use App\Models\FootballMatch;
use App\Models\Team;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AllRoutesHealthTest extends TestCase
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

        $home = Team::create(['name' => 'Arsenal FC', 'short_name' => 'Arsenal']);
        $away = Team::create(['name' => 'Chelsea FC', 'short_name' => 'Chelsea']);

        FootballMatch::create([
            'competition_id' => $comp->id,
            'home_team_id' => $home->id,
            'away_team_id' => $away->id,
            'season' => 2026,
            'matchday' => 1,
            'kickoff_at' => CarbonImmutable::now()->subDays(2),
            'status' => 'finished',
            'home_goals' => 2,
            'away_goals' => 1,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('routeProvider')]
    public function test_route_executes_successfully(string $uri, string $expectedContent): void
    {
        $response = $this->get($uri);

        $response->assertStatus(200);
        $response->assertSee($expectedContent);
    }

    public static function routeProvider(): array
    {
        return [
            'Dashboard' => ['/', 'Premier League'],
            'Today Fixtures Hub' => ['/today', 'Fixtures Hub'],
            'Track Record' => ['/track-record', 'Track Record'],
            'AI Reasoning Studio' => ['/ai-reasoning', 'AI Reasoning Studio'],
            'Man of the Match' => ['/motm', 'Man of the Match'],
            'System Health & Auditor' => ['/system-health', 'System Health'],
            'Settings' => ['/settings', 'Settings'],
            'Themes' => ['/themes', 'Choose your theme'],
            'Model Changelog' => ['/changelog', 'Model Changelog'],
            'Technical Documentation' => ['/docs', 'Technical Documentation'],
        ];
    }
}
