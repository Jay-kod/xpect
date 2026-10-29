<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Competition;
use App\Models\FootballMatch;
use App\Models\MatchStat;
use App\Models\Team;
use App\Services\DataSources\Understat\UnderstatParser;
use App\Services\Teams\TeamResolver;
use Carbon\CarbonImmutable;
use Database\Seeders\CompetitionSeeder;
use Database\Seeders\TeamSeeder;
use Database\Seeders\UnderstatTeamSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnderstatParserTest extends TestCase
{
    use RefreshDatabase;

    protected UnderstatParser $parser;
    protected Competition $pl;
    protected Team $arsenal;
    protected Team $chelsea;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CompetitionSeeder::class);
        $this->seed(TeamSeeder::class);
        $this->seed(UnderstatTeamSeeder::class);

        $this->parser = new UnderstatParser(app(TeamResolver::class));
        $this->pl = Competition::where('slug', 'premier-league')->first();
        $this->arsenal = Team::where('name', 'Arsenal FC')->first();
        $this->chelsea = Team::where('name', 'Chelsea FC')->first();
    }

    public function test_parses_clean_json_and_persists_match_stats(): void
    {
        $kickoff = CarbonImmutable::parse('2024-08-17 14:00:00', 'UTC');
        $match = FootballMatch::create([
            'competition_id' => $this->pl->id,
            'season' => 2024,
            'matchday' => 1,
            'kickoff_at' => $kickoff,
            'home_team_id' => $this->arsenal->id,
            'away_team_id' => $this->chelsea->id,
            'status' => 'finished',
            'home_goals' => 2,
            'away_goals' => 1,
        ]);

        $payload = json_encode([
            'dates' => [
                [
                    'id' => '1001',
                    'isResult' => true,
                    'h' => ['id' => '83', 'title' => 'Arsenal', 'short_title' => 'ARS'],
                    'a' => ['id' => '80', 'title' => 'Chelsea', 'short_title' => 'CHE'],
                    'goals' => ['h' => '2', 'a' => '1'],
                    'xG' => ['h' => '2.15', 'a' => '0.85'],
                    'datetime' => '2024-08-17 14:00:00',
                ],
            ],
        ]);

        $apiCall = \App\Models\ApiCall::create([
            'source' => 'understat',
            'method' => 'GET',
            'url' => 'https://understat.com/getLeagueData/EPL/2024',
            'counts_toward_quota' => false,
            'triggered_by' => 'test',
            'cache_key' => 'test-understat-key',
            'called_at' => CarbonImmutable::now('UTC'),
        ]);

        $result = $this->parser->parseAndPersist($payload, $this->pl, 2024, $apiCall->id);

        $this->assertSame(1, $result['imported']);
        $this->assertSame(0, $result['skipped']);
        $this->assertEmpty($result['unknown_teams']);

        $homeStat = MatchStat::where('match_id', $match->id)
            ->where('team_id', $this->arsenal->id)
            ->first();
        $this->assertNotNull($homeStat);
        $this->assertEquals(2.15, $homeStat->xg);
        $this->assertEquals(0.85, $homeStat->xga);
        $this->assertSame('understat', $homeStat->source);
        $this->assertSame($apiCall->id, $homeStat->source_api_call_id);

        $awayStat = MatchStat::where('match_id', $match->id)
            ->where('team_id', $this->chelsea->id)
            ->first();
        $this->assertNotNull($awayStat);
        $this->assertEquals(0.85, $awayStat->xg);
        $this->assertEquals(2.15, $awayStat->xga);
    }

    public function test_parses_html_with_hex_encoded_script(): void
    {
        $kickoff = CarbonImmutable::parse('2024-08-17 14:00:00', 'UTC');
        $match = FootballMatch::create([
            'competition_id' => $this->pl->id,
            'season' => 2024,
            'matchday' => 1,
            'kickoff_at' => $kickoff,
            'home_team_id' => $this->arsenal->id,
            'away_team_id' => $this->chelsea->id,
            'status' => 'finished',
            'home_goals' => 2,
            'away_goals' => 1,
        ]);

        $jsonStr = json_encode([
            [
                'id' => '1002',
                'isResult' => true,
                'h' => ['title' => 'Arsenal'],
                'a' => ['title' => 'Chelsea'],
                'xG' => ['h' => '1.90', 'a' => '0.60'],
                'datetime' => '2024-08-17 14:00:00',
            ],
        ]);

        // Convert string to hex escaped representation \xHH
        $hexEncoded = '';
        for ($i = 0; $i < strlen($jsonStr); $i++) {
            $hexEncoded .= '\\x' . sprintf('%02X', ord($jsonStr[$i]));
        }

        $html = "<html><head><script>var datesData = JSON.parse('{$hexEncoded}')</script></head><body></body></html>";

        $result = $this->parser->parseAndPersist($html, $this->pl, 2024, null);

        $this->assertSame(1, $result['imported']);

        $homeStat = MatchStat::where('match_id', $match->id)->where('team_id', $this->arsenal->id)->first();
        $this->assertNotNull($homeStat);
        $this->assertEquals(1.90, $homeStat->xg);
        $this->assertEquals(0.60, $homeStat->xga);
    }
}
