<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_calls', function (Blueprint $table) {
            $table->id();
            $table->string('source'); // football_data, api_football, understat, elo, csv, anthropic
            $table->string('method', 10);
            $table->text('url');
            $table->json('request_params')->nullable();
            $table->longText('request_body')->nullable();
            $table->json('request_headers')->nullable(); // REDACTED
            $table->smallInteger('response_status')->nullable();
            $table->longText('response_body')->nullable();
            $table->json('response_headers')->nullable();
            $table->integer('duration_ms')->nullable();
            $table->text('error')->nullable();
            $table->integer('input_tokens')->nullable();
            $table->integer('output_tokens')->nullable();
            $table->boolean('counts_toward_quota')->default(true);
            $table->boolean('served_from_cache')->default(false);
            $table->foreignId('cached_from_call_id')->nullable()->constrained('api_calls')->nullOnDelete();
            $table->string('cache_key');
            $table->string('triggered_by');
            $table->foreignId('sync_run_id')->nullable()->constrained('sync_runs')->nullOnDelete();
            $table->dateTime('called_at');
            $table->timestamps();

            $table->index(['source', 'called_at']);
            $table->index(['cache_key', 'called_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_calls');
    }
};

