<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('match_lineups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')->constrained('matches')->cascadeOnDelete();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->string('formation')->nullable();
            $table->json('starting_xi')->nullable();
            $table->json('substitutes')->nullable();
            $table->string('coach_name')->nullable();
            $table->boolean('confirmed')->default(false);
            $table->string('source')->default('api_football');
            $table->foreignId('source_api_call_id')->nullable()->constrained('api_calls')->nullOnDelete();
            $table->timestamps();

            $table->unique(['match_id', 'team_id', 'source']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('match_lineups');
    }
};
