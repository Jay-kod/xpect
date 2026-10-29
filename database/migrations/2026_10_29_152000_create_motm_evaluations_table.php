<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('motm_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')->constrained('matches')->cascadeOnDelete();
            $table->string('player_name');
            $table->integer('player_number')->nullable();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->string('position')->nullable(); // G, D, M, F
            $table->boolean('is_starter')->default(true);
            $table->boolean('is_motm')->default(false);
            $table->decimal('rating', 3, 1); // e.g. 8.7
            $table->integer('goals')->default(0);
            $table->integer('assists')->default(0);
            $table->decimal('xg', 4, 2)->nullable();
            $table->text('performance_summary');
            $table->string('model')->default('algorithmic-v1');
            $table->foreignId('source_api_call_id')->nullable()->constrained('api_calls')->nullOnDelete();
            $table->timestamps();

            $table->index(['match_id', 'is_motm']);
            $table->index(['player_name', 'is_motm']);
            $table->index(['team_id', 'is_motm']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('motm_evaluations');
    }
};

