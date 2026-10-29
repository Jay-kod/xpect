<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('injuries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->string('fixture_api_id')->nullable();
            $table->string('player_name');
            $table->string('player_type')->nullable();
            $table->string('reason')->nullable();
            $table->string('source')->default('api_football');
            $table->foreignId('source_api_call_id')->nullable()->constrained('api_calls')->nullOnDelete();
            $table->dateTime('as_of');
            $table->timestamps();

            $table->index(['team_id', 'as_of']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('injuries');
    }
};
