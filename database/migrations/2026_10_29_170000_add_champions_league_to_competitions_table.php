<?php

declare(strict_types=1);

use App\Models\Competition;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Competition::updateOrCreate(
            ['slug' => 'champions-league'],
            [
                'name' => 'UEFA Champions League',
                'country' => 'Europe',
                'source_ids' => [
                    'football_data' => 'CL',
                    'football_data_id' => 2001,
                    'api_football' => 2,
                ],
                'is_active' => true,
            ]
        );
    }

    public function down(): void
    {
        Competition::where('slug', 'champions-league')->delete();
    }
};

