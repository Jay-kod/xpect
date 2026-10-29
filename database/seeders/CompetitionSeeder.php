<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Competition;
use Illuminate\Database\Seeder;

class CompetitionSeeder extends Seeder
{
    public function run(): void
    {
        $competitions = [
            [
                'slug' => 'premier-league',
                'name' => 'Premier League',
                'country' => 'England',
                'source_ids' => [
                    'football_data' => 'PL',
                    'football_data_id' => 2021,
                    'api_football' => 39,
                    'csv' => 'E0',
                ],
                'is_active' => true,
            ],
            [
                'slug' => 'la-liga',
                'name' => 'La Liga',
                'country' => 'Spain',
                'source_ids' => [
                    'football_data' => 'PD',
                    'football_data_id' => 2014,
                    'api_football' => 140,
                    'csv' => 'SP1',
                ],
                'is_active' => true,
            ],
            [
                'slug' => 'bundesliga',
                'name' => 'Bundesliga',
                'country' => 'Germany',
                'source_ids' => [
                    'football_data' => 'BL1',
                    'football_data_id' => 2002,
                    'api_football' => 78,
                    'csv' => 'D1',
                ],
                'is_active' => true,
            ],
            [
                'slug' => 'serie-a',
                'name' => 'Serie A',
                'country' => 'Italy',
                'source_ids' => [
                    'football_data' => 'SA',
                    'football_data_id' => 2019,
                    'api_football' => 135,
                    'csv' => 'I1',
                ],
                'is_active' => true,
            ],
            [
                'slug' => 'ligue-1',
                'name' => 'Ligue 1',
                'country' => 'France',
                'source_ids' => [
                    'football_data' => 'FL1',
                    'football_data_id' => 2015,
                    'api_football' => 61,
                    'csv' => 'F1',
                ],
                'is_active' => true,
            ],
            [
                'slug' => 'champions-league',
                'name' => 'UEFA Champions League',
                'country' => 'Europe',
                'source_ids' => [
                    'football_data' => 'CL',
                    'football_data_id' => 2001,
                    'api_football' => 2,
                ],
                'is_active' => true,
            ],
        ];

        foreach ($competitions as $comp) {
            Competition::updateOrCreate(
                ['slug' => $comp['slug']],
                $comp
            );
        }
    }
}

