<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Competition;
use App\Models\FootballMatch;
use App\Models\Prediction;
use App\Models\Team;
use Carbon\CarbonImmutable;
use Database\Seeders\CompetitionSeeder;
use Database\Seeders\TeamSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MatchdayRefreshCommandTest extends TestCase
{
    use RefreshDatabase;

    protected Competition $pl;
    protected Team $arsenal;
    protected Team $chelsea;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CompetitionSeeder::class);
        $this->seed(TeamSeeder::class);

        $this->pl = Competition::where('slug', 'premier-league')->first();
        $this->arsenal = Team::where('name', 'Arsenal FC')->first();
        $this->chelsea = Team::where('name', 'Chelsea FC')->first();
    }

    public function test_matchday_refresh_generates_stage_predictions_and_preserves_immutability(): void
    {
        $today = CarbonImmutable::now('UTC');

        $match = FootballMatch::create([
            'competition_id' => $this->pl->id,
            'season' => 2024,
            'matchday' => 10,
            'kickoff_at' => $today->addHours(4),
            'home_team_id' => $this->arsenal->id,
            'away_team_id' => $this->chelsea->id,
            'status' => 'scheduled',
        ]);

        // 1. Initial pre_match prediction (created earlier in the week)
        $preMatchPred = Prediction::create([
            'match_id' => $match->id,
            'model_version' => 'v1.1.0-xg',
            'stage' => 'pre_match',
            'is_backtest' => false,
            'as_of' => $today->subDays(2),
            'p_home' => 0.50,
            'p_draw' => 0.25,
            'p_away' => 0.25,
            'exp_home_goals' => 1.80,
            'exp_away_goals' => 1.10,
            'features' => ['stage' => 'pre_match'],
            'low_confidence' => false,
        ]);

        $this->assertDatabaseCount('predictions', 1);

        // 2. Run matchday refresh with stage=matchday
        $this->artisan('refresh:matchday', [
            'competition' => 'premier-league',
            '--date' => $today->toDateString(),
            '--stage' => 'matchday',
            '--mock' => true,
        ])->assertSuccessful();

        // Must now have 2 distinct prediction records (immutability preserved)
        $this->assertDatabaseCount('predictions', 2);

        $matchdayPred = Prediction::where('match_id', $match->id)
            ->where('stage', 'matchday')
            ->first();
        $this->assertNotNull($matchdayPred);
        $this->assertSame('matchday', $matchdayPred->stage);
        $this->assertNotSame($preMatchPred->id, $matchdayPred->id);

        // 3. Run final lineup refresh (~1 hour before match)
        $this->artisan('refresh:matchday', [
            'competition' => 'premier-league',
            '--date' => $today->toDateString(),
            '--stage' => 'final',
            '--mock' => true,
        ])->assertSuccessful();

        // Must now have 3 distinct predictions for this match (pre_match, matchday, final)
        $this->assertDatabaseCount('predictions', 3);

        $finalPred = Prediction::where('match_id', $match->id)
            ->where('stage', 'final')
            ->first();
        $this->assertNotNull($finalPred);
        $this->assertSame('final', $finalPred->stage);

        // Assert starting XI was recorded for this match
        $this->assertDatabaseHas('match_lineups', [
            'match_id' => $match->id,
            'confirmed' => true,
        ]);
    }
}
