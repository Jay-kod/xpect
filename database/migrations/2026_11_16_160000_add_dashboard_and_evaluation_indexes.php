<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('matches', function (Blueprint $table): void {
            $table->index(
                ['competition_id', 'season', 'status', 'kickoff_at'],
                'idx_matches_comp_season_status_kickoff'
            );
        });

        Schema::table('prediction_evaluations', function (Blueprint $table): void {
            $table->index('evaluated_at', 'idx_prediction_evaluations_evaluated_at');
        });

        Schema::table('motm_evaluations', function (Blueprint $table): void {
            $table->index(
                ['is_motm', 'verification_status', 'player_name', 'team_id'],
                'idx_motm_verified_player_group'
            );
        });
    }

    public function down(): void
    {
        Schema::table('matches', function (Blueprint $table): void {
            $table->dropIndex('idx_matches_comp_season_status_kickoff');
        });

        Schema::table('prediction_evaluations', function (Blueprint $table): void {
            $table->dropIndex('idx_prediction_evaluations_evaluated_at');
        });

        Schema::table('motm_evaluations', function (Blueprint $table): void {
            $table->dropIndex('idx_motm_verified_player_group');
        });
    }
};
