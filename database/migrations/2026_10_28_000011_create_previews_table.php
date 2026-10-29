<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('previews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prediction_id')->constrained('predictions')->cascadeOnDelete();
            $table->foreignId('api_call_id')->constrained('api_calls')->cascadeOnDelete();
            $table->string('model');
            $table->string('prompt_version');
            $table->longText('text');
            $table->boolean('flagged')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('previews');
    }
};

