<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Competition;
use App\Models\FootballMatch;
use App\Models\Prediction;
use App\Models\Team;
use Database\Seeders\CompetitionSeeder;
use Database\Seeders\TeamSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PredictCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CompetitionSeeder::class);
        $this->seed(TeamSeeder::class);
    }

    public function test_predict_command_generates_and_stores_prediction(): void
    {
        $comp = Competition::where('slug', 'premier-league')->first();
        $arsenal = Team::where('name', 'Arsenal FC')->first();
        $chelsea = Team::where('name', 'Chelsea FC')->first();

        $match = FootballMatch::create([
            'competition_id' => $comp->id,
            'season' => 2026,
            'matchday' => 10,
            'kickoff_at' => now()->addDays(3),
            'home_team_id' => $arsenal->id,
            'away_team_id' => $chelsea->id,
            'status' => 'scheduled',
        ]);

        $this->artisan('predict', ['match' => $match->id])
            ->expectsOutputToContain('Arsenal FC vs Chelsea FC')
            ->expectsOutputToContain('PROBABILITIES:')
            ->expectsOutputToContain('EXPECTED GOALS')
            ->expectsOutputToContain('TOP 5 SCORELINE PREDICTIONS:')
            ->assertSuccessful();

        $prediction = Prediction::where('match_id', $match->id)->first();
        $this->assertNotNull($prediction);
        $this->assertGreaterThan(0, $prediction->p_home);
        $this->assertGreaterThan(0, $prediction->p_draw);
        $this->assertGreaterThan(0, $prediction->p_away);
        $this->assertNotNull($prediction->exp_home_goals);
        $this->assertNotNull($prediction->exp_away_goals);
    }
}

