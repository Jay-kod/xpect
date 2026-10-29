<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Team;
use App\Models\TeamAlias;
use Illuminate\Database\Seeder;

class Season2026PromotedTeamsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Existing team missing football_data alias
        $lens = Team::where('name', 'RC Lens')->first();
        if ($lens) {
            TeamAlias::firstOrCreate([
                'team_id' => $lens->id,
                'source' => 'football_data',
                'alias' => 'Racing Club de Lens',
            ]);
        }

        $newTeams = [
            // Spain (La Liga)
            [
                'name' => 'Real Racing Club de Santander',
                'short_name' => 'Racing Santander',
                'country' => 'Spain',
                'aliases' => [
                    'football_data' => ['Real Racing Club de Santander', 'Racing Santander'],
                    'csv' => ['Racing Santander', 'Santander'],
                ],
            ],
            [
                'name' => 'RC Deportivo La Coruña',
                'short_name' => 'Deportivo',
                'country' => 'Spain',
                'aliases' => [
                    'football_data' => ['RC Deportivo La Coruña', 'Deportivo La Coruna', 'Deportivo'],
                    'csv' => ['Deportivo', 'La Coruna'],
                ],
            ],
            [
                'name' => 'Málaga CF',
                'short_name' => 'Málaga',
                'country' => 'Spain',
                'aliases' => [
                    'football_data' => ['Málaga CF', 'Malaga CF', 'Malaga'],
                    'csv' => ['Malaga'],
                ],
            ],

            // Germany (Bundesliga)
            [
                'name' => 'SC Paderborn 07',
                'short_name' => 'Paderborn',
                'country' => 'Germany',
                'aliases' => [
                    'football_data' => ['SC Paderborn 07', 'Paderborn'],
                    'csv' => ['Paderborn'],
                ],
            ],
            [
                'name' => 'SV 07 Elversberg',
                'short_name' => 'Elversberg',
                'country' => 'Germany',
                'aliases' => [
                    'football_data' => ['SV 07 Elversberg', 'Elversberg'],
                    'csv' => ['Elversberg'],
                ],
            ],
            [
                'name' => 'Hamburger SV',
                'short_name' => 'Hamburg',
                'country' => 'Germany',
                'aliases' => [
                    'football_data' => ['Hamburger SV', 'Hamburg'],
                    'csv' => ['Hamburg', 'HSV'],
                ],
            ],

            // France (Ligue 1)
            [
                'name' => 'Le Mans FC',
                'short_name' => 'Le Mans',
                'country' => 'France',
                'aliases' => [
                    'football_data' => ['Le Mans FC', 'Le Mans'],
                    'csv' => ['Le Mans'],
                ],
            ],
            [
                'name' => 'Paris FC',
                'short_name' => 'Paris FC',
                'country' => 'France',
                'aliases' => [
                    'football_data' => ['Paris FC'],
                    'csv' => ['Paris FC'],
                ],
            ],
        ];

        foreach ($newTeams as $tData) {
            $team = Team::firstOrCreate(
                ['name' => $tData['name']],
                ['short_name' => $tData['short_name'], 'country' => $tData['country']]
            );

            foreach ($tData['aliases'] as $source => $aliases) {
                foreach ($aliases as $alias) {
                    TeamAlias::firstOrCreate([
                        'team_id' => $team->id,
                        'source' => $source,
                        'alias' => $alias,
                    ]);
                }
            }
        }
    }
}

