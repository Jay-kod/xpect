<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * High-efficiency database indexes to accelerate web page loads,
     * temporal resolution, MOTM leaderboards, and relationship lookups.
     */
    public function up(): void
    {
        Schema::table('predictions', function (Blueprint $table) {
            $table->index(['match_id', 'is_backtest', 'created_at'], 'idx_predictions_match_backtest_created');
            $table->index(['is_backtest', 'created_at'], 'idx_predictions_backtest_created');
        });

        Schema::table('previews', function (Blueprint $table) {
            $table->index(['prediction_id', 'created_at'], 'idx_previews_prediction_created');
        });

        Schema::table('motm_evaluations', function (Blueprint $table) {
            $table->index(['is_motm', 'player_name'], 'idx_motm_is_motm_player');
            $table->index(['is_motm', 'team_id'], 'idx_motm_is_motm_team');
            $table->index(['is_motm', 'rating'], 'idx_motm_is_motm_rating');
            $table->index(['match_id', 'rating'], 'idx_motm_match_rating');
        });

        Schema::table('matches', function (Blueprint $table) {
            $table->index(['competition_id', 'kickoff_at'], 'idx_matches_comp_kickoff');
            $table->index(['competition_id', 'season', 'status', 'matchday'], 'idx_matches_comp_season_status_md');
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->index('name', 'idx_teams_name');
            $table->index('short_name', 'idx_teams_short_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('predictions', function (Blueprint $table) {
            $table->dropIndex('idx_predictions_match_backtest_created');
            $table->dropIndex('idx_predictions_backtest_created');
        });

        Schema::table('previews', function (Blueprint $table) {
            $table->dropIndex('idx_previews_prediction_created');
        });

        Schema::table('motm_evaluations', function (Blueprint $table) {
            $table->dropIndex('idx_motm_is_motm_player');
            $table->dropIndex('idx_motm_is_motm_team');
            $table->dropIndex('idx_motm_is_motm_rating');
            $table->dropIndex('idx_motm_match_rating');
        });

        Schema::table('matches', function (Blueprint $table) {
            $table->dropIndex('idx_matches_comp_kickoff');
            $table->dropIndex('idx_matches_comp_season_status_md');
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->dropIndex('idx_teams_name');
            $table->dropIndex('idx_teams_short_name');
        });
    }
};
