<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('motm_evaluations', function (Blueprint $table) {
            $table->decimal('data_rating', 3, 1)->nullable()->after('rating');
            $table->decimal('decision_score', 3, 1)->nullable()->after('data_rating');
            $table->json('decisive_actions')->nullable()->after('assists');
            $table->string('decision_rationale')->nullable()->after('performance_summary');
        });
    }

    public function down(): void
    {
        Schema::table('motm_evaluations', function (Blueprint $table) {
            $table->dropColumn(['data_rating', 'decision_score', 'decisive_actions', 'decision_rationale']);
        });
    }
};

