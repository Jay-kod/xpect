<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_id')->constrained('competitions')->cascadeOnDelete();
            $table->smallInteger('season'); // e.g. 2024 for 2024/25
            $table->smallInteger('matchday')->nullable();
            $table->dateTime('kickoff_at')->index();
            $table->foreignId('home_team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('away_team_id')->constrained('teams')->cascadeOnDelete();
            $table->string('status'); // scheduled, live, finished, postponed, cancelled
            $table->smallInteger('home_goals')->nullable();
            $table->smallInteger('away_goals')->nullable();
            $table->smallInteger('ht_home_goals')->nullable();
            $table->smallInteger('ht_away_goals')->nullable();
            $table->string('football_data_id')->nullable()->unique();
            $table->string('api_football_id')->nullable()->unique();
            $table->string('understat_id')->nullable()->unique();
            $table->foreignId('source_api_call_id')->nullable()->constrained('api_calls')->nullOnDelete();
            $table->timestamps();

            $table->index(['competition_id', 'season', 'kickoff_at']);
            $table->index(['home_team_id', 'kickoff_at']);
            $table->index(['away_team_id', 'kickoff_at']);
            $table->index(['status', 'kickoff_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matches');
    }
};

