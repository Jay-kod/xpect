<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_aliases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->string('source'); // football_data, api_football, understat, csv, elo
            $table->string('alias');
            $table->timestamps();

            $table->unique(['source', 'alias']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_aliases');
    }
};

