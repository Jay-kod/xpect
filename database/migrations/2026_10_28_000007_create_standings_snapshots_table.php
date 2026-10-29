<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('standings_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_id')->constrained('competitions')->cascadeOnDelete();
            $table->smallInteger('season');
            $table->smallInteger('matchday');
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->smallInteger('position');
            $table->smallInteger('played');
            $table->smallInteger('won');
            $table->smallInteger('drawn');
            $table->smallInteger('lost');
            $table->smallInteger('goals_for');
            $table->smallInteger('goals_against');
            $table->smallInteger('points');
            $table->dateTime('as_of');
            $table->foreignId('source_api_call_id')->nullable()->constrained('api_calls')->nullOnDelete();
            $table->timestamps();

            $table->unique(['competition_id', 'season', 'matchday', 'team_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('standings_snapshots');
    }
};

