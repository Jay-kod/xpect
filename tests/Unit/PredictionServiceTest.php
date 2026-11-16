<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Competition;
use App\Models\FootballMatch;
use App\Models\MatchLineup;
use App\Models\Prediction;
use App\Models\Team;
use App\Services\Models\PoissonModel;
use App\Services\Predictions\PredictionService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PredictionServiceTest extends TestCase
{
    use RefreshDatabase;

    private PredictionService $service;
    private Competition $comp;
    private Team $home;
    private Team $away;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(PredictionService::class);

        $this->comp = Competition::create([
            'slug' => 'premier-league',
            'name' => 'Premier League',
            'country' => 'England',
            'is_active' => true,
        ]);

        $this->home = Team::create(['name' => 'Arsenal FC', 'short_name' => 'Arsenal']);
        $this->away = Team::create(['name' => 'Chelsea FC', 'short_name' => 'Chelsea']);
    }

    public function test_determine_stage_resolves_pre_match_when_kickoff_is_days_ahead(): void
    {
        $match = FootballMatch::create([
            'competition_id' => $this->comp->id,
            'home_team_id' => $this->home->id,
            'away_team_id' => $this->away->id,
            'season' => 2026,
            'matchday' => 10,
            'kickoff_at' => CarbonImmutable::now('UTC')->addDays(4),
            'status' => 'scheduled',
        ]);

        $stage = $this->service->determineStage($match, CarbonImmutable::now('UTC'));
        $this->assertSame('pre_match', $stage);
    }

    public function test_determine_stage_resolves_matchday_when_kickoff_is_today(): void
    {
        $match = FootballMatch::create([
            'competition_id' => $this->comp->id,
            'home_team_id' => $this->home->id,
            'away_team_id' => $this->away->id,
            'season' => 2026,
            'matchday' => 10,
            'kickoff_at' => CarbonImmutable::now('UTC')->addHours(4),
            'status' => 'scheduled',
        ]);

        $stage = $this->service->determineStage($match, CarbonImmutable::now('UTC'));
        $this->assertSame('matchday', $stage);
    }

    public function test_determine_stage_resolves_final_when_lineups_confirmed(): void
    {
        $match = FootballMatch::create([
            'competition_id' => $this->comp->id,
            'home_team_id' => $this->home->id,
            'away_team_id' => $this->away->id,
            'season' => 2026,
            'matchday' => 10,
            'kickoff_at' => CarbonImmutable::now('UTC')->addHours(1),
            'status' => 'scheduled',
        ]);

        MatchLineup::create([
            'match_id' => $match->id,
            'team_id' => $this->home->id,
            'confirmed' => true,
            'source' => 'api_football',
        ]);

        MatchLineup::create([
            'match_id' => $match->id,
            'team_id' => $this->away->id,
            'confirmed' => true,
            'source' => 'api_football',
        ]);

        $stage = $this->service->determineStage($match, CarbonImmutable::now('UTC'));
        $this->assertSame('final', $stage);
    }

    public function test_predict_returns_existing_prediction_without_violating_unique_constraint(): void
    {
        $match = FootballMatch::create([
            'competition_id' => $this->comp->id,
            'home_team_id' => $this->home->id,
            'away_team_id' => $this->away->id,
            'season' => 2026,
            'matchday' => 10,
            'kickoff_at' => CarbonImmutable::now('UTC')->addDays(3),
            'status' => 'scheduled',
        ]);

        $pred1 = $this->service->predict($match, CarbonImmutable::now('UTC'), false, PoissonModel::MODEL_VERSION, 'pre_match');
        $this->assertNotNull($pred1);
        $this->assertDatabaseCount('predictions', 1);

        // Calling predict again for the same model and stage must return pred1 without SQL 23000 error
        $pred2 = $this->service->predict($match, CarbonImmutable::now('UTC'), false, PoissonModel::MODEL_VERSION, 'pre_match');
        $this->assertSame($pred1->id, $pred2->id);
        $this->assertDatabaseCount('predictions', 1);
    }
}
