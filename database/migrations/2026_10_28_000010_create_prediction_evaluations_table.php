<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prediction_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prediction_id')->unique()->constrained('predictions')->cascadeOnDelete();
            $table->string('outcome', 1); // H, D, A
            $table->string('predicted_outcome', 1); // H, D, A
            $table->boolean('correct');
            $table->decimal('log_loss', 8, 4);
            $table->decimal('brier', 8, 4);
            $table->dateTime('evaluated_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prediction_evaluations');
    }
};

