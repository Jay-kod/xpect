<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('match_odds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')->constrained('matches')->cascadeOnDelete();
            $table->string('bookmaker'); // e.g. b365, market_avg, pinnacle
            $table->decimal('home_odds', 8, 3);
            $table->decimal('draw_odds', 8, 3);
            $table->decimal('away_odds', 8, 3);
            $table->decimal('implied_home_prob', 8, 4);
            $table->decimal('implied_draw_prob', 8, 4);
            $table->decimal('implied_away_prob', 8, 4);
            $table->decimal('overround', 6, 4)->nullable(); // e.g. 1.0542
            $table->string('source')->default('csv'); // csv, api_football
            $table->foreignId('source_api_call_id')->nullable()->constrained('api_calls')->nullOnDelete();
            $table->timestamps();

            $table->unique(['match_id', 'bookmaker', 'source']);
            $table->index(['match_id', 'bookmaker']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('match_odds');
    }
};
