<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('predictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')->constrained('matches')->cascadeOnDelete();
            $table->string('model_version');
            $table->boolean('is_backtest')->default(false);
            $table->dateTime('as_of');
            $table->decimal('p_home', 6, 4);
            $table->decimal('p_draw', 6, 4);
            $table->decimal('p_away', 6, 4);
            $table->decimal('exp_home_goals', 5, 2);
            $table->decimal('exp_away_goals', 5, 2);
            $table->json('top_scorelines')->nullable();
            $table->json('features');
            $table->boolean('low_confidence')->default(false);
            $table->timestamps();

            $table->unique(['match_id', 'model_version', 'is_backtest']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('predictions');
    }
};

