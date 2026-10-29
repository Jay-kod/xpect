<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->string('rating_type'); // e.g. elo
            $table->decimal('rating', 8, 2);
            $table->date('as_of');
            $table->foreignId('source_api_call_id')->nullable()->constrained('api_calls')->nullOnDelete();
            $table->timestamps();

            $table->unique(['team_id', 'rating_type', 'as_of']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_ratings');
    }
};

