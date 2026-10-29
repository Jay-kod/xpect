<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('match_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')->constrained('matches')->cascadeOnDelete();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->decimal('xg', 5, 2)->nullable();
            $table->decimal('xga', 5, 2)->nullable();
            $table->unsignedSmallInteger('shots')->nullable();
            $table->unsignedSmallInteger('shots_on_target')->nullable();
            $table->string('source');
            $table->foreignId('source_api_call_id')->nullable()->constrained('api_calls')->nullOnDelete();
            $table->timestamps();

            $table->unique(['match_id', 'team_id', 'source']);
            $table->index(['team_id', 'source']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('match_stats');
    }
};
