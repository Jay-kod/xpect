<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Team;
use App\Models\TeamAlias;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    public function run(): void
    {
        $teams = [
            [
                'name' => 'Arsenal FC',
                'short_name' => 'Arsenal',
                'country' => 'England',
                'aliases' => [
                    ['source' => 'football_data', 'alias' => 'Arsenal FC'],
                    ['source' => 'football_data', 'alias' => 'Arsenal'],
                    ['source' => 'csv', 'alias' => 'Arsenal'],
                    ['source' => 'api_football', 'alias' => 'Arsenal'],
                ],
            ],
            [
                'name' => 'Aston Villa FC',
                'short_name' => 'Aston Villa',
                'country' => 'England',
                'aliases' => [
                    ['source' => 'football_data', 'alias' => 'Aston Villa FC'],
                    ['source' => 'football_data', 'alias' => 'Aston Villa'],
                    ['source' => 'csv', 'alias' => 'Aston Villa'],
                    ['source' => 'api_football', 'alias' => 'Aston Villa'],
                ],
            ],
            [
                'name' => 'AFC Bournemouth',
                'short_name' => 'Bournemouth',
                'country' => 'England',
                'aliases' => [
                    ['source' => 'football_data', 'alias' => 'AFC Bournemouth'],
                    ['source' => 'football_data', 'alias' => 'Bournemouth'],
                    ['source' => 'csv', 'alias' => 'Bournemouth'],
                    ['source' => 'api_football', 'alias' => 'Bournemouth'],
                ],
            ],
            [
                'name' => 'Brentford FC',
                'short_name' => 'Brentford',
                'country' => 'England',
                'aliases' => [
                    ['source' => 'football_data', 'alias' => 'Brentford FC'],
                    ['source' => 'football_data', 'alias' => 'Brentford'],
                    ['source' => 'csv', 'alias' => 'Brentford'],
                    ['source' => 'api_football', 'alias' => 'Brentford'],
                ],
            ],
            [
                'name' => 'Brighton & Hove Albion FC',
                'short_name' => 'Brighton',
                'country' => 'England',
                'aliases' => [
                    ['source' => 'football_data', 'alias' => 'Brighton & Hove Albion FC'],
                    ['source' => 'football_data', 'alias' => 'Brighton Hove'],
                    ['source' => 'csv', 'alias' => 'Brighton'],
                    ['source' => 'api_football', 'alias' => 'Brighton'],
                ],
            ],
            [
                'name' => 'Burnley FC',
                'short_name' => 'Burnley',
                'country' => 'England',
                'aliases' => [
                    ['source' => 'football_data', 'alias' => 'Burnley FC'],
                    ['source' => 'football_data', 'alias' => 'Burnley'],
                    ['source' => 'csv', 'alias' => 'Burnley'],
                    ['source' => 'api_football', 'alias' => 'Burnley'],
                ],
            ],
            [
                'name' => 'Chelsea FC',
                'short_name' => 'Chelsea',
                'country' => 'England',
                'aliases' => [
                    ['source' => 'football_data', 'alias' => 'Chelsea FC'],
                    ['source' => 'football_data', 'alias' => 'Chelsea'],
                    ['source' => 'csv', 'alias' => 'Chelsea'],
                    ['source' => 'api_football', 'alias' => 'Chelsea'],
                ],
            ],
            [
                'name' => 'Coventry City FC',
                'short_name' => 'Coventry City',
                'country' => 'England',
                'aliases' => [
                    ['source' => 'football_data', 'alias' => 'Coventry City FC'],
                    ['source' => 'football_data', 'alias' => 'Coventry City'],
                    ['source' => 'csv', 'alias' => 'Coventry'],
                    ['source' => 'api_football', 'alias' => 'Coventry'],
                ],
            ],
            [
                'name' => 'Crystal Palace FC',
                'short_name' => 'Crystal Palace',
                'country' => 'England',
                'aliases' => [
                    ['source' => 'football_data', 'alias' => 'Crystal Palace FC'],
                    ['source' => 'football_data', 'alias' => 'Crystal Palace'],
                    ['source' => 'csv', 'alias' => 'Crystal Palace'],
                    ['source' => 'api_football', 'alias' => 'Crystal Palace'],
                ],
            ],
            [
                'name' => 'Everton FC',
                'short_name' => 'Everton',
                'country' => 'England',
                'aliases' => [
                    ['source' => 'football_data', 'alias' => 'Everton FC'],
                    ['source' => 'football_data', 'alias' => 'Everton'],
                    ['source' => 'csv', 'alias' => 'Everton'],
                    ['source' => 'api_football', 'alias' => 'Everton'],
                ],
            ],
            [
                'name' => 'Fulham FC',
                'short_name' => 'Fulham',
                'country' => 'England',
                'aliases' => [
                    ['source' => 'football_data', 'alias' => 'Fulham FC'],
                    ['source' => 'football_data', 'alias' => 'Fulham'],
                    ['source' => 'csv', 'alias' => 'Fulham'],
                    ['source' => 'api_football', 'alias' => 'Fulham'],
                ],
            ],
            [
                'name' => 'Hull City AFC',
                'short_name' => 'Hull City',
                'country' => 'England',
                'aliases' => [
                    ['source' => 'football_data', 'alias' => 'Hull City AFC'],
                    ['source' => 'football_data', 'alias' => 'Hull City'],
                    ['source' => 'csv', 'alias' => 'Hull'],
                    ['source' => 'api_football', 'alias' => 'Hull City'],
                ],
            ],
            [
                'name' => 'Ipswich Town FC',
                'short_name' => 'Ipswich Town',
                'country' => 'England',
                'aliases' => [
                    ['source' => 'football_data', 'alias' => 'Ipswich Town FC'],
                    ['source' => 'football_data', 'alias' => 'Ipswich Town'],
                    ['source' => 'csv', 'alias' => 'Ipswich'],
                    ['source' => 'api_football', 'alias' => 'Ipswich'],
                ],
            ],
            [
                'name' => 'Leeds United FC',
                'short_name' => 'Leeds United',
                'country' => 'England',
                'aliases' => [
                    ['source' => 'football_data', 'alias' => 'Leeds United FC'],
                    ['source' => 'football_data', 'alias' => 'Leeds United'],
                    ['source' => 'csv', 'alias' => 'Leeds'],
                    ['source' => 'api_football', 'alias' => 'Leeds'],
                ],
            ],
            [
                'name' => 'Leicester City FC',
                'short_name' => 'Leicester',
                'country' => 'England',
                'aliases' => [
                    ['source' => 'football_data', 'alias' => 'Leicester City FC'],
                    ['source' => 'football_data', 'alias' => 'Leicester'],
                    ['source' => 'csv', 'alias' => 'Leicester'],
                    ['source' => 'api_football', 'alias' => 'Leicester'],
                ],
            ],
            [
                'name' => 'Liverpool FC',
                'short_name' => 'Liverpool',
                'country' => 'England',
                'aliases' => [
                    ['source' => 'football_data', 'alias' => 'Liverpool FC'],
                    ['source' => 'football_data', 'alias' => 'Liverpool'],
                    ['source' => 'csv', 'alias' => 'Liverpool'],
                    ['source' => 'api_football', 'alias' => 'Liverpool'],
                ],
            ],
            [
                'name' => 'Luton Town FC',
                'short_name' => 'Luton Town',
                'country' => 'England',
                'aliases' => [
                    ['source' => 'football_data', 'alias' => 'Luton Town FC'],
                    ['source' => 'football_data', 'alias' => 'Luton Town'],
                    ['source' => 'csv', 'alias' => 'Luton'],
                    ['source' => 'api_football', 'alias' => 'Luton'],
                ],
            ],
            [
                'name' => 'Manchester City FC',
                'short_name' => 'Man City',
                'country' => 'England',
                'aliases' => [
                    ['source' => 'football_data', 'alias' => 'Manchester City FC'],
                    ['source' => 'football_data', 'alias' => 'Man City'],
                    ['source' => 'csv', 'alias' => 'Man City'],
                    ['source' => 'api_football', 'alias' => 'Manchester City'],
                ],
            ],
            [
                'name' => 'Manchester United FC',
                'short_name' => 'Man United',
                'country' => 'England',
                'aliases' => [
                    ['source' => 'football_data', 'alias' => 'Manchester United FC'],
                    ['source' => 'football_data', 'alias' => 'Man United'],
                    ['source' => 'csv', 'alias' => 'Man United'],
                    ['source' => 'api_football', 'alias' => 'Manchester United'],
                ],
            ],
            [
                'name' => 'Newcastle United FC',
                'short_name' => 'Newcastle',
                'country' => 'England',
                'aliases' => [
                    ['source' => 'football_data', 'alias' => 'Newcastle United FC'],
                    ['source' => 'football_data', 'alias' => 'Newcastle'],
                    ['source' => 'csv', 'alias' => 'Newcastle'],
                    ['source' => 'api_football', 'alias' => 'Newcastle'],
                ],
            ],
            [
                'name' => 'Norwich City FC',
                'short_name' => 'Norwich',
                'country' => 'England',
                'aliases' => [
                    ['source' => 'football_data', 'alias' => 'Norwich City FC'],
                    ['source' => 'football_data', 'alias' => 'Norwich'],
                    ['source' => 'csv', 'alias' => 'Norwich'],
                    ['source' => 'api_football', 'alias' => 'Norwich'],
                ],
            ],
            [
                'name' => 'Nottingham Forest FC',
                'short_name' => 'Nottingham',
                'country' => 'England',
                'aliases' => [
                    ['source' => 'football_data', 'alias' => 'Nottingham Forest FC'],
                    ['source' => 'football_data', 'alias' => 'Nottingham'],
                    ['source' => 'csv', 'alias' => "Nott'm Forest"],
                    ['source' => 'csv', 'alias' => 'Nottingham Forest'],
                    ['source' => 'api_football', 'alias' => 'Nottingham Forest'],
                ],
            ],
            [
                'name' => 'Sheffield United FC',
                'short_name' => 'Sheffield Utd',
                'country' => 'England',
                'aliases' => [
                    ['source' => 'football_data', 'alias' => 'Sheffield United FC'],
                    ['source' => 'football_data', 'alias' => 'Sheffield Utd'],
                    ['source' => 'csv', 'alias' => 'Sheffield United'],
                    ['source' => 'api_football', 'alias' => 'Sheffield Utd'],
                ],
            ],
            [
                'name' => 'Southampton FC',
                'short_name' => 'Southampton',
                'country' => 'England',
                'aliases' => [
                    ['source' => 'football_data', 'alias' => 'Southampton FC'],
                    ['source' => 'football_data', 'alias' => 'Southampton'],
                    ['source' => 'csv', 'alias' => 'Southampton'],
                    ['source' => 'api_football', 'alias' => 'Southampton'],
                ],
            ],
            [
                'name' => 'Sunderland AFC',
                'short_name' => 'Sunderland',
                'country' => 'England',
                'aliases' => [
                    ['source' => 'football_data', 'alias' => 'Sunderland AFC'],
                    ['source' => 'football_data', 'alias' => 'Sunderland'],
                    ['source' => 'csv', 'alias' => 'Sunderland'],
                    ['source' => 'api_football', 'alias' => 'Sunderland'],
                ],
            ],
            [
                'name' => 'Tottenham Hotspur FC',
                'short_name' => 'Tottenham',
                'country' => 'England',
                'aliases' => [
                    ['source' => 'football_data', 'alias' => 'Tottenham Hotspur FC'],
                    ['source' => 'football_data', 'alias' => 'Tottenham'],
                    ['source' => 'csv', 'alias' => 'Tottenham'],
                    ['source' => 'api_football', 'alias' => 'Tottenham'],
                ],
            ],
            [
                'name' => 'Watford FC',
                'short_name' => 'Watford',
                'country' => 'England',
                'aliases' => [
                    ['source' => 'football_data', 'alias' => 'Watford FC'],
                    ['source' => 'football_data', 'alias' => 'Watford'],
                    ['source' => 'csv', 'alias' => 'Watford'],
                    ['source' => 'api_football', 'alias' => 'Watford'],
                ],
            ],
            [
                'name' => 'West Bromwich Albion FC',
                'short_name' => 'West Brom',
                'country' => 'England',
                'aliases' => [
                    ['source' => 'football_data', 'alias' => 'West Bromwich Albion FC'],
                    ['source' => 'football_data', 'alias' => 'West Brom'],
                    ['source' => 'csv', 'alias' => 'West Brom'],
                    ['source' => 'api_football', 'alias' => 'West Brom'],
                ],
            ],
            [
                'name' => 'West Ham United FC',
                'short_name' => 'West Ham',
                'country' => 'England',
                'aliases' => [
                    ['source' => 'football_data', 'alias' => 'West Ham United FC'],
                    ['source' => 'football_data', 'alias' => 'West Ham'],
                    ['source' => 'csv', 'alias' => 'West Ham'],
                    ['source' => 'api_football', 'alias' => 'West Ham'],
                ],
            ],
            [
                'name' => 'Wolverhampton Wanderers FC',
                'short_name' => 'Wolverhampton',
                'country' => 'England',
                'aliases' => [
                    ['source' => 'football_data', 'alias' => 'Wolverhampton Wanderers FC'],
                    ['source' => 'football_data', 'alias' => 'Wolverhampton'],
                    ['source' => 'csv', 'alias' => 'Wolves'],
                    ['source' => 'api_football', 'alias' => 'Wolves'],
                ],
            ],
        ];

        foreach ($teams as $teamData) {
            $aliases = $teamData['aliases'] ?? [];
            unset($teamData['aliases']);

            $team = Team::updateOrCreate(
                ['name' => $teamData['name']],
                $teamData
            );

            foreach ($aliases as $alias) {
                TeamAlias::updateOrCreate(
                    [
                        'source' => $alias['source'],
                        'alias' => $alias['alias'],
                    ],
                    [
                        'team_id' => $team->id,
                    ]
                );
            }
        }
    }
}

