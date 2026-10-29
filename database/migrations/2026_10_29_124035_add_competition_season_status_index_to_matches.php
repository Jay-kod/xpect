<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    /**
     * Add a composite index covering the dashboard's most common query pattern:
     * WHERE competition_id = ? AND season = ? AND status = ?
     * This single index supports the count aggregation, KPI stats, and
     * status-filtered paginated listing queries.
     */
    public function up(): void
    {
        Schema::table('matches', function (Blueprint $table) {
            $table->index(
                ['competition_id', 'season', 'status'],
                'matches_competition_season_status_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('matches', function (Blueprint $table) {
            $table->dropIndex('matches_competition_season_status_index');
        });
    }
};
