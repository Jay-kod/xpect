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
            $table->string('verification_status')->default('algorithmic')->after('model');
            $table->json('source_urls')->nullable()->after('verification_status');
            $table->json('source_titles')->nullable()->after('source_urls');
            $table->json('research_queries')->nullable()->after('source_titles');
            $table->timestamp('verified_at')->nullable()->after('research_queries');
        });

        Schema::table('motm_evaluations', function (Blueprint $table) {
            $table->decimal('rating', 3, 1)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('motm_evaluations', function (Blueprint $table) {
            $table->dropColumn(['verification_status', 'source_urls', 'source_titles', 'research_queries', 'verified_at']);
        });
    }
};
